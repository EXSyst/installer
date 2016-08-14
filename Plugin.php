<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use EXSyst\Installer\Symfony\Configurator\BundleConfigurator;

/**
 * @internal
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    private $enabled = false;
    private $configurators;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->enabled = true;
    }

    public function onCommand(CommandEvent $event)
    {
        if ('require' !== $event->getCommandName()) {
            $this->enabled = false;
        }
    }

    public function onPostPackageInstall(PackageEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $io = $event->getIO();
        $project = Project::fromPackageEvent($event);
        $installedPackage = $project->getInstalledPackage();
        foreach ($this->getConfigurators() as $configurator) {
            if ($configurator->supports($project)) {
                if ($io->askConfirmation(sprintf('<info>Configure "%s"?</info> [<comment>no</comment>]: ', $installedPackage->getName()), false)) {
                    $configurator->configure($project);
                    $io->write('');
                } else {
                    $io->write([sprintf('Installation of "%s" skipped.', $installedPackage->getName()), '']);
                }

                return;
            }
        }

        $io->write([sprintf('No configurator found for "%s".', $installedPackage->getName()), '']);
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::COMMAND => 'onCommand',
            PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageInstall',
        ];
    }

    private function getConfigurators(): array
    {
        if (null === $this->configurators) {
            $this->configurators = [
                new BundleConfigurator(),
            ];
        }

        return $this->configurators;
    }
}
