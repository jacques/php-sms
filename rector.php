<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    // Define what rule sets will be applied
    $parameters->set(Option::SETS, [
        SetList::CODE_QUALITY,
        SetList::CODE_QUALITY_STRICT,
        SetList::CODING_STYLE,
        //SetList::DEAD_CODE,
        SetList::PERFORMANCE,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::PHP_73,
        SetList::PHP_74,
        SetList::PHPUNIT_70,
        SetList::PHPUNIT_75,
        SetList::PHPUNIT_80,
        SetList::PHPUNIT_90,
        SetList::PHPUNIT_91,
        SetList::PHP_CODE_SNIFFER_30,
    ]);

    // get services (needed for register a single rule)
    // $services = $containerConfigurator->services();

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};
