<?php

namespace Wulfheart\LaravelActionsIdeHelper\Service;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\AsJob;
use Lorisleiva\Actions\Concerns\AsListener;
use Lorisleiva\Actions\Concerns\AsObject;
use Lorisleiva\Lody\Lody;
use phpDocumentor\Reflection\Exception;
use phpDocumentor\Reflection\File\LocalFile;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\File;
use phpDocumentor\Reflection\Php\Project;
use phpDocumentor\Reflection\Php\ProjectFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ActionInfoFactory
{
    /**
     * @return array<ActionInfo>
     */
    public static function create(array $paths): array
    {
        $factory = new self();
        $validPaths = array_filter($paths, fn($path) => is_dir($path));
        $classes = collect($validPaths)->flatMap(fn ($path) => $factory->loadFromPath($path))->toArray();
        $classMap = collect($validPaths)->flatMap(fn ($path) => $factory->loadPhpDocumentorReflectionClassMap($path))->toArray();
        $ais = [];
        foreach ($classes as $class => $traits) {
            $tc = collect($traits);
            $ais[] = ActionInfo::create()
                ->setName($class)
                ->setAsObject($tc->contains(AsObject::class))
                ->setAsCommand($tc->contains(AsCommand::class))
                ->setAsController($tc->contains(AsController::class))
                ->setAsJob($tc->contains(AsJob::class))
                ->setAsListener($tc->contains(AsListener::class))
                ->setClassInfo($classMap[$class]);
        }
        return $ais;
    }

    /**
     * @return array<class-string,array<class-string>>
     */
    protected function loadFromPath(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $res = Lody::classes($path)->isNotAbstract();

        /** @var array<class-string,array<class-string>> $traits */
        return collect(ActionInfo::ALL_TRAITS)
            ->map(fn ($trait, $key) => [$trait => $res->hasTrait($trait)->all()])
            ->collapse()
            ->map(function ($item, $key) {
                return collect($item)
                    ->map(fn ($i) => [
                        'item' => $i,
                        'group' => $key,
                    ])
                    ->toArray();
            })
            ->values()
            ->collapse()
            ->groupBy('item')
            ->map(fn ($item) => $item->pluck('group')->toArray())
            ->toArray();
    }

    /**
     * @return array<Class_>
     *
     * @throws Exception
     */
    protected function loadPhpDocumentorReflectionClassMap(string $path): array
    {
        $finder = Finder::create()->files()->in($path)->name('*.php');
        $files = collect($finder)->map(fn (SplFileInfo $file) => new LocalFile($file->getRealPath()))->toArray();

        /** @var Project $project */
        $project = ProjectFactory::createInstance()->create('Laravel Actions IDE Helper', $files);

        return collect($project->getFiles())
            ->map(fn (File $f) => $f->getClasses())
            ->collapse()
            ->mapWithKeys(fn ($item, string $key) => [Str::of($key)->ltrim('\\')->toString() => $item])
            ->toArray();

    }
}
