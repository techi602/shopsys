<?php

declare(strict_types=1);

use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SETS,
        [
            SetList::PSR_12,
        ]
    );

    $parameters->set(
        Option::SKIP,
        [
            ObjectIsCreatedByFactorySniff::class => [
                __DIR__, '/tests/*',
            ],
        ]
    );

    // this package is meant to be extensible using class inheritance, so we want to avoid private visibilities in the model namespace
    $services->set('forbidden_private_visibility_fixer.product_feed_google', ForbiddenPrivateVisibilityFixer::class)
        ->call('configure', [['analyzed_namespaces' => ['Shopsys\ProductFeed\GoogleBundle\Model']]]);

    $services->set(ForceLateStaticBindingForProtectedConstantsSniff::class);

    $containerConfigurator->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};
