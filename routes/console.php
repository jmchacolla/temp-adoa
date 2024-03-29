<?php
use Illuminate\Support\Facades\Schema;
Artisan::command('package-zj-adoa:install', function () {
    if (!Schema::hasTable('sample_skeleton')) {
        Schema::create('sample_skeleton', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('status', ['ENABLED', 'DISABLED'])->default('ENABLED');
            $table->timestamps();
        });
    }
    Artisan::call('vendor:publish', [
        '--tag' => 'package-zj-adoa',
        '--force' => true
    ]);

    $this->info('Package Zj Adoa has been installed');
})->describe('Installs the required js files and table in DB');


