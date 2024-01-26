<?php

namespace ProcessMaker\Package\Adoa\Console\Commands;

use Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ProcessMaker\Console\PackageInstallCommand;

class Install extends PackageInstallCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer-adoa:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the required js files and table in DB';

    /**
     * Publish assets
     * @return void
     */
    public function publishAssets()
    {
        $this->info('Publishing Assets');
        Artisan::call('vendor:publish', [
            '--tag' => 'adoa',
            '--force' => true
        ]);
    }

    public function preinstall()
    {
        $this->publishAssets();
    }

    /**
     * Run migrations for the package
     * @return void
     */
    public function addDatabaseTables()
    {
        $this->info('Updating database for the package');
        // if (!Schema::hasTable('sample_skeleton')) {
        //     Schema::create('sample_skeleton', function (Blueprint $table) {
        //         $table->increments('id');
        //         $table->string('name');
        //         $table->enum('status', ['ENABLED', 'DISABLED'])->default('ENABLED');
        //         $table->timestamps();
        //     });
        // }

        if (!Schema::hasTable('adoa_employee_appraisal')) {
            Schema::create('adoa_employee_appraisal', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();
                $table->string('user_ein')->nullable();
                $table->integer('evaluator_id')->nullable();
                $table->integer('supervisor_id')->nullable();
                $table->string('supervisor_ein')->nullable();
                $table->integer('type')->nullable()->index()->comment('1=Employee Coaching Note; 2=Manager Coaching Note; 3=Employee Self-Appraisal; 4=Informal Manager Appraisal for Employee; 5=Formal Manager Appraisal for Employee;');
                $table->mediumText('content')->nullable();
                $table->dateTime('date', 0)->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamps();

            });
        }

        if (!Schema::hasColumn('adoa_employee_appraisal', 'request_id')) {
            Schema::table('adoa_employee_appraisal', function (Blueprint $table) {
                $table->integer('request_id')->after('id')->nullable();
            });
        }


        if (!Schema::hasTable('adoa_type_appraisal_detail')) {
            Schema::create('adoa_type_appraisal_detail', function (Blueprint $table) {
                $table->increments('id');
                $table->string('description')->nullable();
                $table->timestamps();
            });

            DB::table('adoa_type_appraisal_detail')->insert(
                [
                    ['id'=>1,'description' => 'Employee Coaching Note'],
                    ['id'=>2,'description' => 'Manager Coaching Note'],
                    ['id'=>3,'description' => 'Employee Self-Appraisal'],
                    ['id'=>4,'description' => 'Informal Manager Appraisal for Employee'],
                    ['id'=>5,'description' => 'Formal Manager Appraisal for Employee']
                ]
            );
        }

        if (!Schema::hasTable('adoa_user_information')) {
            Schema::create('adoa_user_information', function (Blueprint $table) {
                $table->integer('user_id');
                $table->string('position')->nullable();
                $table->string('manager')->nullable();
                $table->string('super_position')->nullable();
                $table->string('title')->nullable();
                $table->string('ein')->nullable();
                $table->string('agency')->nullable();
                $table->string('agency_name')->nullable();
                $table->string('process_level')->nullable();
                $table->string('department')->nullable();
                $table->timestamps();
            });
        }
    }

    public function install()
    {
        $this->addDatabaseTables();
    }

    public function postinstall()
    {
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();
        $this->info('Adoa has been installed');
    }
}
