<?php

namespace Wulfheart\LaravelActionsIdeHelper\Service\Generator\DocBlock;

use phpDocumentor\Reflection\DocBlock\Tag;
use Wulfheart\LaravelActionsIdeHelper\Service\ActionInfo;

interface DocBlockGeneratorInterface
{
    public static function create(): self;

    /**
     * @return Tag[]
     */
    public function generate(ActionInfo $info): array;
}
