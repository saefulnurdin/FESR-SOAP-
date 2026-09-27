import Alpine from 'alpinejs';
import recorder from './recorder';

window.Alpine = Alpine;

Alpine.data('recorder', recorder);

Alpine.start();
