<?php

// src/TeiEditionBundle.php

namespace TeiEditionBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class TeiEditionBundle extends AbstractBundle
{
    protected string $extensionAlias = 'tei_edition';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->stringNode('public_dir')
                    ->cannotBeEmpty()
                    ->defaultValue('%kernel.project_dir%/public')
                    ->info('Path to the public directory (default: %kernel.project_dir%/public)')
                ->end()
                ->arrayNode('imagemagick')
                    ->children()
                        ->arrayNode('processor')
                            ->children()
                                ->stringNode('path')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end() // imagemagick
                ->variableNode('pdf_generator') // TODO: explicitely specify keys
                ->end() // pdf-generator
                ->arrayNode('xsl')
                    ->children()
                        ->stringNode('cache')
                        ->end()
                        ->arrayNode('commandline')
                            ->children()
                                ->stringNode('template')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('saxon')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultFalse()
                                    ->info('When enabled, built-in saxonc-extension will be use.')
                                ->end()
                    ->end()
                ->end() // xsl
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // set parameters defined in config/packages/tei_edition.yaml
        $builder->setParameter(
            join('.', [$this->extensionAlias, 'public_dir']),
            $config['public_dir']
        );

        $builder->setParameter(
            join('.', [$this->extensionAlias, 'imagemagick.processor.arguments']),
            array_key_exists('imagemagick', $config) && array_key_exists('processor', $config['imagemagick']) && is_array($config['imagemagick']['processor'])
                ? $config['imagemagick']['processor'] : []
        );

        $builder->setParameter(
            join('.', [$this->extensionAlias, 'pdf_generator.arguments']),
            array_key_exists('pdf_generator', $config) && is_array($config['pdf_generator'])
                ? $config['pdf_generator'] : []
        );

        // xsl
        $builder->setParameter(
            join('.', [$this->extensionAlias, 'xsl', 'saxonc_enabled']),
            array_key_exists('xsl', $config) && array_key_exists('saxon', $config['xsl']) && array_key_exists('enabled', $config['xsl']['saxon'])
            ? $config['xsl']['saxon']['enabled'] : false
        );

        $builder->setParameter(
            join('.', [$this->extensionAlias, 'xsl', 'commandline.template']),
            array_key_exists('xsl', $config) && array_key_exists('commandline', $config['xsl']) && array_key_exists('template', $config['xsl']['commandline'])
            && !empty($config['xsl']['commandline']['template'])
                ? $config['xsl']['commandline']['template']
                : 'java -jar %kernel.project_dir%/bin/saxon9he.jar -s:%%source%% -xsl:%%xsl%% %%additional%%'
        );

        $xslAdapter = 'tei_edition.xsl.commandline_adapter';
        if (array_key_exists('xsl', $config) && array_key_exists('cache', $config['xsl']) && !empty($config['xsl']['cache'])) {
            if (!str_starts_with($config['xsl']['cache'], '@')) {
                throw new InvalidArgumentException(sprintf('tei_edition.xsl.cache must be a service (given "%s").', $config['xsl']['cache']));
            }

            // adds a new "tei_edition.xsl.cached_commandline_adapter"
            $definition = new Definition(\TeiEditionBundle\Utils\Xsl\XsltCacheAdapter::class, [
                new Reference($xslAdapter),
                new Reference(ltrim($config['xsl']['cache'], '@')),
            ]);
            $builder->setDefinition($xslAdapter = 'tei_edition.xsl.cached_commandline_adapter', $definition);
        }

        $builder->setParameter(
            join('.', [$this->extensionAlias, 'xsl', 'cache_enabled']),
            'tei_edition.xsl.cached_commandline_adapter' == $xslAdapter
        );

        // load a YAML file
        $container->import('../config/services.yaml');
    }
}
