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

use Dunglas\ActionBundle\DependencyInjection\DunglasActionExtension;
use EXSyst\Installer\Project;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;

/**
 * @internal
 */
final class DunglasActionConfigurator extends BundleConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure(Project $project)
    {
        if (!parent::configure($project)) {
            return;
        }

        if (!$this->shouldBeConfigured($project)) {
            return;
        }

        $io = $project->getIO();
        $appConfig = $this->getAppConfig($project);
        $defaultDirectories = $appConfig['directories'];

        $directories = [];
        while (true) {
            $message = '<info>Register a new directory?</info> ';
            $default = null;
            if (count($defaultDirectories)) {
                $default = array_shift($defaultDirectories);
                $message .= sprintf('[<comment>%s</comment>]: ', $default);
            }

            $directory = $io->ask($message, $default);
            if (null !== $directory && 'none' !== $directory) {
                $directories[] = $directory;
            } else {
                if (null === $default) {
                    break;
                }
            }
        }

        $config = $this->getConfig($project);
        // If not already the current config
        if ($directories !== $appConfig['directories']) {
            $config['directories'] = $directories;
        }

        $this->saveConfig($config, $project);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Project $project): bool
    {
        $package = $project->getConfiguredPackage();

        return 'dunglas/action-bundle' === $package->getName() && parent::supports($project);
    }

    protected function getExtension(string $bundle): ConfigurationExtensionInterface
    {
        return new DunglasActionExtension();
    }
}
