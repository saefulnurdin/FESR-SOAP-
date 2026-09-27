/**
 * Perekam audio di peramban.
 *
 * Menggabungkan MediaRecorder untuk merekam dan AnalyserNode untuk
 * menghitung level suara, karena keduanya harus membaca aliran media yang
 * sama. Batas durasi ditegakkan di sini karena server tidak dapat memverifikasi
 * panjang berkas tanpa ffmpeg.
 *
 * Status:
 *   idle     belum ada perekaman berjalan
 *   recording sedang merekam
 *   paused   dijeda, dapat dilanjutkan
 *   ready    berkas siap diunggah, dapat didengarkan atau diulang
 */
export default function recorder(config = {}) {
    return {
        state: 'idle',
        elapsed: 0,
        level: 0,
        error: '',
        mimeType: '',
        blobUrl: '',
        durationSeconds: 0,
        file: null,
        uploading: false,
        progress: 0,

        maxDuration: config.maxDuration ?? 600,
        maxSizeKb: config.maxSizeKb ?? 51200,
        autoStop: false,

        recorder: null,
        stream: null,
        audioContext: null,
        analyser: null,
        levelFrame: null,
        timer: null,
        chunks: [],

        /**
         * Format yang didukung peramban ini, diurutkan dari yang terbaik.
         *
         * @returns {string[]}
         */
        get supportedTypes() {
            return [
                'audio/webm;codecs=opus',
                'audio/webm',
                'audio/ogg;codecs=opus',
                'audio/ogg',
                'audio/mp4',
                'audio/mpeg',
                'audio/wav',
            ];
        },

        init() {
            this.mimeType = this.supportedTypes.find((type) =>
                window.MediaRecorder?.isTypeSupported?.(type),
            ) ?? '';
        },

        /**
         * Ask for the microphone and start recording.
         */
        async start() {
            this.error = '';

            if (! navigator.mediaDevices?.getUserMedia || ! window.MediaRecorder) {
                this.error = 'Peramban ini belum mendukung perekaman audio. Gunakan Chrome, Edge, atau Firefox.';

                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch {
                this.error = 'Izin mikrofon tidak diberikan. Izinkan akses mikrofon di peramban lalu coba lagi.';

                return;
            }

            this.attachLevelMeter();
            this.chunks = [];
            this.elapsed = 0;
            this.autoStop = false;

            this.recorder = new MediaRecorder(this.stream, {
                ...(this.mimeType ? { mimeType: this.mimeType } : {}),
            });

            this.recorder.addEventListener('dataavailable', (event) => {
                if (event.data && event.data.size > 0) {
                    this.chunks.push(event.data);
                }
            });

            this.recorder.addEventListener('stop', () => this.finish());

            this.recorder.start(1000);
            this.state = 'recording';
            this.startTimer();
        },

        /**
         * Pause without losing what has been captured.
         */
        pause() {
            if (this.state !== 'recording' || !this.recorder) {
                return;
            }

            this.recorder.pause();
            this.stopTimer();
            this.state = 'paused';
        },

        /**
         * Continue after a pause.
         */
        resume() {
            if (this.state !== 'paused' || !this.recorder) {
                return;
            }

            this.recorder.resume();
            this.state = 'recording';
            this.startTimer();
        },

        /**
         * Stop and keep the result for review.
         */
        stop() {
            if (!this.recorder || !['recording', 'paused'].includes(this.state)) {
                return;
            }

            this.recorder.requestData();
            this.recorder.stop();
        },

        /**
         * Throw away the current recording and start over.
         */
        discard() {
            this.stopTimer();
            this.releaseStream();
            this.clearPreview();
            this.state = 'idle';
            this.error = '';
        },

        /**
         * Stop and immediately prepare a fresh take.
         */
        async rerecord() {
            this.discard();
            await this.start();
        },

        /**
         * Assemble the recorded chunks into a file ready to upload.
         */
        finish() {
            this.stopTimer();
            this.releaseStream();

            const type = this.chunks[0]?.type || this.mimeType || 'audio/webm';

            this.file = new File(this.chunks, `rekaman-${Date.now()}.${this.extensionFor(type)}`, { type });
            this.blobUrl = URL.createObjectURL(this.file);
            this.durationSeconds = this.elapsed;
            this.state = 'ready';
        },

        /**
         * Upload the recording as multipart form data.
         *
         * XHR dipakai, bukan fetch, karena unggahan perlu menampilkan
         * progres yang benar-benar berasal dari server.
         *
         * @returns {Promise<boolean>} True when the server accepted the file.
         */
        submit() {
            if (!this.file || this.uploading) {
                return Promise.resolve(false);
            }

            const form = this.$refs.form;
            const data = new FormData(form);
            data.set('audio', this.file, this.file.name);

            this.uploading = true;
            this.progress = 0;
            this.error = '';

            return new Promise((resolve) => {
                const xhr = new XMLHttpRequest();
                xhr.open(form.method, form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        this.progress = Math.round((event.loaded / event.total) * 100);
                    }
                });

                xhr.addEventListener('load', () => {
                    this.uploading = false;

                    if (xhr.status >= 200 && xhr.status < 400) {
                        window.location.assign(xhr.responseURL || form.dataset.redirect);

                        return;
                    }

                    this.error = this.errorMessage(xhr);
                    resolve(false);
                });

                xhr.addEventListener('error', () => {
                    this.uploading = false;
                    this.error = 'Koneksi terputus saat mengunggah rekaman.';
                    resolve(false);
                });

                xhr.send(data);
            });
        },

        /**
         * Turn an error response into a message worth showing.
         *
         * @param {XMLHttpRequest} xhr
         * @returns {string}
         */
        errorMessage(xhr) {
            try {
                const payload = JSON.parse(xhr.responseText);

                if (payload?.errors) {
                    return Object.values(payload.errors).flat().join(' ');
                }

                if (payload?.message) {
                    return payload.message;
                }
            } catch {
                // Falls through to the generic message below.
            }

            return 'Rekaman gagal disimpan. Periksa ukuran berkas lalu coba lagi.';
        },

        /**
         * Start the level meter on the same stream being recorded.
         */
        attachLevelMeter() {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;

            if (!AudioContextClass) {
                return;
            }

            this.audioContext = new AudioContextClass();
            const source = this.audioContext.createMediaStreamSource(this.stream);

            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 512;
            source.connect(this.analyser);

            const samples = new Uint8Array(this.analyser.fftSize);

            const read = () => {
                this.analyser.getByteTimeDomainData(samples);

                let peak = 0;
                for (const sample of samples) {
                    peak = Math.max(peak, Math.abs(sample - 128) / 128);
                }

                this.level = Math.min(100, Math.round(peak * 140));
                this.levelFrame = requestAnimationFrame(read);
            };

            read();
        },

        /**
         * Count elapsed time and stop automatically at the limit.
         */
        startTimer() {
            this.stopTimer();

            this.timer = setInterval(() => {
                this.elapsed += 1;

                if (this.elapsed >= this.maxDuration) {
                    this.autoStop = true;
                    this.stop();
                }
            }, 1000);
        },

        stopTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        /**
         * Release the microphone and the audio graph.
         */
        releaseStream() {
            if (this.levelFrame) {
                cancelAnimationFrame(this.levelFrame);
                this.levelFrame = null;
            }

            this.stream?.getTracks().forEach((track) => track.stop());
            this.stream = null;

            this.audioContext?.close().catch(() => {});
            this.audioContext = null;
            this.analyser = null;
            this.level = 0;
        },

        clearPreview() {
            if (this.blobUrl) {
                URL.revokeObjectURL(this.blobUrl);
            }

            this.blobUrl = '';
            this.file = null;
            this.elapsed = 0;
            this.durationSeconds = 0;
        },

        /**
         * Elapsed time as mm:ss.
         */
        get formattedTime() {
            const minutes = Math.floor(this.elapsed / 60);
            const seconds = this.elapsed % 60;

            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },

        /**
         * Remaining allowance as mm:ss, shown while recording.
         */
        get remainingTime() {
            const left = Math.max(0, this.maxDuration - this.elapsed);
            const minutes = Math.floor(left / 60);
            const seconds = left % 60;

            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },

        /**
         * Derive a file extension from the recorded MIME type.
         *
         * @param {string} type
         * @returns {string}
         */
        extensionFor(type) {
            if (type.includes('webm')) {
                return 'webm';
            }

            if (type.includes('ogg')) {
                return 'ogg';
            }

            if (type.includes('mp4')) {
                return 'mp4';
            }

            if (type.includes('mpeg') || type.includes('mp3')) {
                return 'mp3';
            }

            return type.includes('wav') ? 'wav' : 'bin';
        },
    };
}
