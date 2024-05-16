<?php

namespace Wulfheart\LaravelActionsIdeHelper\Service\Generator\DocBlock;

use Lorisleiva\Actions\Concerns\AsObject;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\Php\Method;
use Wulfheart\LaravelActionsIdeHelper\Service\ActionInfo;

class AsObjectGenerator extends DocBlockGeneratorBase implements DocBlockGeneratorInterface
{
    protected string $context = AsObject::class;

    /**
     * @return Tag[]
     */
    public function generate(ActionInfo $info): array
    {
        /** @var Method $method */
        $method = $this->findMethod($info, 'handle');
        if(null === $method){
            return [];
        }

        $parameters = $method->getArguments();

        return [
            new Custom\Method('run', $parameters, $method->getReturnType(), true)
        ];
    }
}
