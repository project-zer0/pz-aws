<?php

declare(strict_types=1);

namespace ProjectZer0\PzAWS;

use ProjectZer0\Pz\Config\PzModuleConfigurationInterface;
use ProjectZer0\Pz\Module\PzModule;
use ProjectZer0\Pz\ProjectZer0Toolkit;
use ProjectZer0\PzAWS\Command\AWSCommand;
use ProjectZer0\PzAWS\Command\AWSLoginCommand;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author Aurimas Niekis <aurimas@niekis.lt>
 */
class PzAWSModule extends PzModule
{
    public function getCommands(): array
    {
        return [
            new AWSCommand(),
            new AWSLoginCommand(),
        ];
    }

    public function boot(ProjectZer0Toolkit $toolkit): void
    {
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function getConfiguration(): ?PzModuleConfigurationInterface
    {
        return new class() implements PzModuleConfigurationInterface {
            public function getConfigurationNode(): NodeDefinition
            {
                $treeBuilder = new TreeBuilder('aws');

                $node = $treeBuilder->getRootNode();

                $node
                    ->children()
                        ->scalarNode('image')
                            ->defaultValue('amazon/aws-cli')
                        ->end()
                        ->scalarNode('config_dir')
                            ->defaultValue('$PZ_PWD/.pz/.aws')
                        ->end()
                    ->end();

                return $node;
            }
        };
    }

    public function getName(): string
    {
        return 'aws';
    }
}
