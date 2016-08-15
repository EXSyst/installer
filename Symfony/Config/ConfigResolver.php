<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Symfony\Config;

use EXSyst\Installer\Project;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @author Guilhem N. <egetick@gmail.com>
 *
 * @internal
 */
class ConfigResolver
{
    private $loader;

    public function getConfig(string $configFile, ConfigurationExtensionInterface $extension, Project $project)
    {
        $container = new ExtensionAwareContainerBuilder($extension);
        $loader = $this->getLoader($container, $project);
        $loader->load($configFile, pathinfo($configFile, PATHINFO_EXTENSION));

        $configs = $container->getConfigs();

        $configuration = $extension->getConfiguration($configs, $container);
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }

    private function getLoader(ContainerBuilder $container, Project $project)
    {
        $locator = new FileLocator($project);
        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
        ]);

        return new DelegatingLoader($resolver);
    }
}

/**
 * @internal
 */
class ExtensionAwareContainerBuilder extends ContainerBuilder
{
    private $configs = [];
    private $extension;

    public function __construct(ConfigurationExtensionInterface $extension)
    {
        parent::__construct(new DummyParameterBag());
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromExtension($extension, array $values = [])
    {
        if ($this->isFrozen()) {
            throw new \LogicException('Cannot load from an extension on a frozen container.');
        }

        if ($extension !== $this->extension->getAlias() && $extension !== $this->extension->getNamespace()) {
            return;
        }

        $this->configs[] = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExtension($name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension($name)
    {
        return new Extension();
    }

    public function getConfigs()
    {
        return $this->configs;
    }
}

/**
 * @internal
 */
class DummyParameterBag extends ParameterBag
{
    public function get($name)
    {
        return '%'.$name.'%';
    }

    public function has($name)
    {
        return true;
    }

    public function resolve()
    {
    }

    public function resolveValue($value)
    {
        return $value;
    }
}

/**
 * @internal
 */
class Extension
{
    public function getXsdValidationBasePath()
    {
        return false;
    }
}
