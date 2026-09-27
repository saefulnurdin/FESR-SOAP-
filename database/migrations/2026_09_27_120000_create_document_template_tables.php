<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Empat tabel ini membentuk satu kesatuan: katalog jenis dokumen, template
     * yang dimiliki jenis dokumen tersebut, serta struktur section dan field
     * milik setiap template. Tabel dokumen dan catatan klinis pada fase
     * berikutnya akan menunjuk ke document_templates.
     */
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });

        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['document_type_id', 'is_active']);
        });

        Schema::create('document_template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained()->cascadeOnDelete();
            $table->string('key', 60);
            $table->string('title');
            $table->text('hint')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['document_template_id', 'key'], 'template_sections_template_key_unique');
            $table->index(['document_template_id', 'sort_order'], 'template_sections_template_order_index');
        });

        Schema::create('document_template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_section_id')->constrained()->cascadeOnDelete();
            $table->string('key', 60);
            $table->string('label');
            $table->string('type', 20)->default('text');
            $table->json('options')->nullable();
            $table->string('unit', 30)->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['document_template_section_id', 'key'], 'template_fields_section_key_unique');
            $table->index(['document_template_section_id', 'sort_order'], 'template_fields_section_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_template_fields');
        Schema::dropIfExists('document_template_sections');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_types');
    }
};
