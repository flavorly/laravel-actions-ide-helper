<?php

namespace Wulfheart\LaravelActionsIdeHelper\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Wulfheart\LaravelActionsIdeHelper\Service\ActionInfoFactory;
use Wulfheart\LaravelActionsIdeHelper\Service\BuildIdeHelper;

class LaravelActionsIdeHelperCommand extends Command
{
    public $signature = 'ide-helper:actions';

    public $description = 'Generate a new IDE Helper file for Laravel Actions.';

    public function handle(): int
    {
        $defaultActionsPaths = [
            app_path('Actions'),
            base_path('Modules'),
            base_path('modules'),
            base_path('Domain'),
            base_path('src'),
        ];
        $defaultOutputFile = base_path('_ide_helper_actions.php');
        $actionsPath = config('laravel-actions-ide-helper.paths', $defaultActionsPaths);
        $outfile = config('laravel-actions-ide-helper.file_name', $defaultOutputFile);

        $actionInfos = ActionInfoFactory::create($actionsPath);

        $result = BuildIdeHelper::create()->build($actionInfos);

        file_put_contents($outfile, $result);

        $this->comment('IDE Helpers generated for Laravel Actions at '.Str::of($outfile));

        return static::SUCCESS;
    }
}
