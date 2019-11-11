<?php

namespace Pyxeel\AutoCrud\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AutoCrudInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autocrud:init {model} {--c=true} {--r=true} {--m=false} {--s=false} {--softdeletes=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generator of CRUD';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = $this->argument('model');
        $softdeletes = $this->option('softdeletes');
        $controller = $this->option('c');
        $request = $this->option('r');
        $migracion = $this->option('m');
        $seeder = $this->option('s');

        $this->comment("Creating Model...");
        $this->model($model, $softdeletes);

        if ($controller) {
            $this->comment("Creating Controller...");
            $this->controller($model);
        }

        if ($request) {
            $this->comment("Creating Request...");
            $this->request($model);
        }

        if ($migracion) {
            $this->comment("Creating Migration...");
            $this->migration($model, $softdeletes);
        }

        if ($seeder) {
            $this->comment("Creating Seeder...");
            $this->seeder($model);
        }

        File::append(base_path('routes/web.php'), 'Route::resource(\'' . Str::plural(strtolower($model)) . "', '{$model}Controller');");

        $this->info("CRUD generated successful !");
    }

    protected function getStubPath($type)
    {
        return __DIR__ . "/../stubs/{$type}.stub";
    }

    protected function getStub($type)
    {
        return file_get_contents($this->getStubPath($type));
    }

    protected function model($name, $softdeletes)
    {
        $softdeletesImport = '';
        $softdeletesTrait = '';

        if ($softdeletes) {
            $softdeletesImport = 'use Illuminate\Database\Eloquent\SoftDeletes;';
            $softdeletesTrait = 'use SoftDeletes;';
        }

        $modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{softdeletesimport}}',
                '{{softdeletestrait}}'
            ],
            [
                $name,
                $softdeletesImport,
                $softdeletesTrait,
            ],
            $this->getStub('Model')
        );

        file_put_contents(app_path("/{$name}.php"), $modelTemplate);
    }

    protected function controller($name)
    {
        $controllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name)
            ],
            $this->getStub('Controller')
        );

        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"), $controllerTemplate);
    }

    protected function request($name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Request')
        );

        if (!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $requestTemplate);
    }

    protected function migration($name, $softdeletes)
    {
        $softdeletesTemplate = '';

        $pluralName = Str::plural($name);

        $pluralNameLowerCase = strtolower(Str::plural($name));

        if ($softdeletes)
            $softdeletesTemplate = '$table->softDeletes();';

        $migrationTemplate = str_replace(
            [
                '{{modelNamePluralLowerCase}}',
                '{{modelNamePlural}}',
                '{{softdeletes}}'
            ],
            [
                $pluralNameLowerCase,
                $pluralName,
                $softdeletesTemplate
            ],
            $this->getStub('Migration')
        );

        $time = date('Y_m_d_His');

        file_put_contents(base_path("/database/migrations/{$time}_create_{$pluralNameLowerCase}_table.php"), $migrationTemplate);
    }

    protected function seeder($name)
    {
        $seederTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Seeder')
        );

        file_put_contents(base_path("/database/seeds/{$name}TableSeeder.php"), $seederTemplate);
    }
}
