<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Symfony\Configurator;

use EXSyst\Installer\Configurator\ConfiguratorInterface;
use EXSyst\Installer\Project;
use EXSyst\Installer\Symfony\Config\ConfigResolver;
use EXSyst\Installer\Symfony\KernelFinder;
use EXSyst\Installer\Symfony\KernelManipulator;
use EXSyst\Installer\Util\ClassFinder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;

class BundleConfigurator implements ConfiguratorInterface
{
    private $bundle;
    private $configFile;
    private $shouldBeConfigured;

    /**
     * {@inheritdoc}
     *
     * @return bool true if the bundle is registered in the kernel, false otherwise.
     */
    public function configure(Project $project): bool
    {
        // Reset values
        $this->bundle = null;
        $this->configFile = null;
        $this->shouldBeConfigured = null;

        $bundle = $this->getBundle($project);
        if (null === $bundle) {
            $io->write('<error>No bundle found.</error>');

            return false;
        }

        $io = $project->getIO();
        $kernelFinder = new KernelFinder();
        $kernel = $kernelFinder->findKernel($project);
        if (null === $kernel) {
            $io->write('<error>No kernel found.</error>');
        }

        $kernelManipulator = new KernelManipulator($kernel);
        if ($kernelManipulator->hasBundle($bundle)) {
            $io->write(sprintf('<info>The bundle "%s" is already registered in "%s".</info>', $bundle, $kernel));

            return true;
        }

        if (!$io->askConfirmation(sprintf('<info>Add the bundle "%s" to your kernel "%s"?</info> [<comment>yes</comment>]: ', $bundle, $kernel))) {
            return false;
        }

        if ($kernelManipulator->addBundle($bundle)) {
            $message = 'has been registered';
        } else {
            $message = 'registration failed';
        }

        $io->write(sprintf('<comment>%s</comment> %s in <comment>%s</comment>.', $bundle, $message, $kernel));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Project $project): bool
    {
        $package = $project->getConfiguredPackage();
        if ('symfony-bundle' !== $package->getType()) {
            return false;
        }

        // symfony project if a kernel can be found
        $kernelFinder = new KernelFinder();

        return null !== $kernelFinder->findKernel($project);
    }

    final protected function shouldBeConfigured(Project $project)
    {
        if (null === $this->shouldBeConfigured) {
            $io = $project->getIO();
            $package = $project->getConfiguredPackage();
            $this->shouldBeConfigured = $io->ask(sprintf('<info>Do you want to configure the most important features of "%s"?</info> [<comment>no</comment>]: ', $package->getName()), false);
        }

        return $this->shouldBeConfigured;
    }

    final protected function saveConfig($config, Project $project, string $bundle = null, string $configFile = null)
    {
        list($extension, $configFile) = $this->getDefault($project, $bundle, $configFile);
        $yaml = Yaml::parse(file_get_contents($configFile));
        $yaml[$extension->getAlias()] = $config;

        file_put_contents($configFile, Yaml::dump($yaml));

        $project->getIO()->write(sprintf("\n".'<comment>"%s" has been updated! We advise you to keep at least two generated files: config_generated.yml and config_dev_generated.yml. You should import in them your own config files (config.yml and config_dev.yml) to let the plugin be aware of them and you should update your kernel to use the generated files.</comment>'."\n", $configFile));
    }

    final protected function getConfig(Project $project, string $bundle = null, string $configFile = null, $default = []): array
    {
        list($extension, $configFile) = $this->getDefault($project, $bundle, $configFile);
        $configResolver = new ConfigResolver();

        if (file_exists($configFile)) {
            $yaml = Yaml::parse(file_get_contents($configFile));

            if (isset($yaml[$extension->getAlias()])) {
                return $yaml[$extension->getAlias()];
            }
        }

        return $default;
    }

    final protected function getAppConfig(Project $project, string $bundle = null, string $configFile = null): array
    {
        list($extension, $configFile) = $this->getDefault($project, $bundle, $configFile);
        $configResolver = new ConfigResolver();

        return $configResolver->getConfig($project, $extension, $configFile);
    }

    protected function getExtension(string $bundle): ConfigurationExtensionInterface
    {
        throw new \LogicException(sprintf('You must override %s to be able to use methods related to config.', __METHOD__));
    }

    private function getDefault(Project $project, string $bundle = null, string $configFile = null)
    {
        if (null === $configFile) {
            $configFile = $this->getConfigFile($project);
        }
        if (null === $bundle) {
            $bundle = $this->getBundle($project);
        }
        $extension = $this->getExtension($bundle);

        return [$extension, $configFile];
    }

    private function getConfigFile(Project $project)
    {
        if (null === $this->configFile) {
            $extra = $project->getRootPackage()->getExtra();
            if (isset($extra['symfony-app-dir'])) {
                $default = '/'.$extra['symfony-app-dir'].'/config/config_generated.yml';
            } else {
                $default = '/app/config/config_generated.yml';
            }

            $io = $project->getIO();
            $io->write("\n".'<comment>Warning: any formatting in the config file generated by the library will be lost. Ensure that the file you choose doesn\'t contain important comments. Only Yaml is supported for now.</comment>'."\n");

            $result = $io->ask(sprintf('<info>In which file do you want to save your config?</info> [<comment>%s</comment>]:', $default), $default);

            $this->configFile = $project->getRootPackagePath().$result;
            touch($this->configFile);
        }

        return $this->configFile;
    }

    /**
     * @return string|null the bundle class
     */
    private function getBundle(Project $project)
    {
        if (null === $this->bundle) {
            $finder = new Finder();
            $finder
                ->in($project->getConfiguredPackagePath())
                ->name('*Bundle.php');
            $classFinder = new ClassFinder();

            $this->bundle = $classFinder->findClass($finder, BundleInterface::class);
        }

        return $this->bundle;
    }
}
