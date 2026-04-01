<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Symfony\CodeQuality\Rector\Class_\InlineClassRoutePrefixRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
        rectorPreset: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    // autodiscover rector rules and sets for your composer.json
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true
    )
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/tests',
        __DIR__ . '/public',
    ])
    ->withSkip([
        __DIR__ . '/vendor',
        ArrayToFirstClassCallableRector::class => [
            __DIR__ . '/src/Security/Authenticator/AzureEntra/Me/MeRequestBuilder.php',
        ],
        InlineClassRoutePrefixRector::class,
        SimplifyBoolIdenticalTrueRector::class,
        RenameParamToMatchTypeRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        SimplifyQuoteEscapeRector::class,
        SimplifyUselessVariableRector::class,
        RenamePropertyToMatchTypeRector::class,
        //        ConvertStaticToSelfRector
    ])
    ->withPhpSets(php84: true)
    ->withParallel(300, 4, 8)
    // register single rule
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class,
    ])
    ->withSymfonyContainerPhp(
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.php'
    )
    ->withAttributesSets();
