<?php

namespace Pyxeel\AutoCrud\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Pyxeel\AutoCrud\Model;

class AutoCrudCompleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autocrud:complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generator of full CRUD';

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
        $this->comment("Creating Scaffolding...");

        $config = include $this->config();

        foreach ($config as $modelName => $configs) {

            $slug = $this->option('slug');

            $model = new Model($modelName, $configs);

            $attributes = $model->getAttributeString();

            $rules = $model->getRulesString();

            $migrations = $model->getMigrations();

            $this->model($modelName, $attributes, $rules, $slug);

            $this->controller($modelName);

            $this->request($modelName, $attributes);

            $this->migration($modelName, $migrations, $slug);

            $this->seeder($modelName);

            File::append(base_path('routes/web.php'), 'Route::resource(\'' . Str::plural(strtolower($modelName)) . "', '{$modelName}Controller');");
        }

        $this->info("CRUD generated successful !");
    }

    protected function config()
    {
        return config_path('AutoCrud.php');
    }

    protected function getStubPath($type)
    {
        return __DIR__ . "/../stubs/complete/{$type}.stub";
    }

    protected function getStub($type)
    {
        return file_get_contents($this->getStubPath($type));
    }

    protected function model($name, $attributes, $rules, $slug = true)
    {
        $slugImports = '';

        $slugCode = '';

        if ($slug) {
            $slugImports = "use Spatie\Sluggable\HasSlug;
        use Spatie\Sluggable\SlugOptions;\n";
            $slugCode = "
    /**
    * Get the options for generating the slug.
    */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['id']) // Change this
            ->saveSlugsTo('slug');
    }";
        }

        $modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{attributes}}',
                '{{rules}}',
                '{{slugImports}}',
                '{{slugCode}}'
            ],
            [
                $name,
                $attributes,
                $rules,
                $slugImports,
                $slugCode
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

    protected function request($name, $attributesString)
    {
        $rules = '';

        if ($attributesString)
            $rules = $name . "::rules();";
        else
            $rules = "[
                // Rules...
            ];";

        $requestTemplate = str_replace(
            ['{{modelName}}', '{{rules}}'],
            [$name, $rules],
            $this->getStub('Request')
        );

        if (!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $requestTemplate);
    }

    protected function migration($name, $migrations, $slug = true)
    {
        $pluralName = Str::plural($name);

        $slug = $slug ? '$table->string("slug");' . "\n" : '';

        $pluralNameLowerCase = strtolower(Str::plural($name));

        $softdeletesTemplate = '$table->softDeletes();';

        $migrationTemplate = str_replace(
            [
                '{{modelNamePluralLowerCase}}',
                '{{modelNamePlural}}',
                '{{softdeletes}}',
                '{{migrations}}',
                '{{slug}}'
            ],
            [
                $pluralNameLowerCase,
                $pluralName,
                $softdeletesTemplate,
                $migrations,
                $slug
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
