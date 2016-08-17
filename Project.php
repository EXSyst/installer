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

use Composer\Config;
use Composer\Composer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;

final class Project
{
    private $composer;
    private $io;
    private $configuredPackage;
    private $configuredPackagePath;

    /**
     * @internal
     */
    public function __construct(Composer $composer, IOInterface $io, PackageInterface $configuredPackage)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->configuredPackage = $configuredPackage;
    }

    /**
     * @internal
     */
    public static function fromPackageEvent(PackageEvent $event): self
    {
        $installedPackage = $event->getOperation()->getPackage();

        return new self($event->getComposer(), $event->getIO(), $installedPackage);
    }

    public function getIO(): IOInterface
    {
        return $this->io;
    }

    public function getRootPackage(): RootPackageInterface
    {
        return $this->composer->getPackage();
    }

    public function getRootPackagePath(): string
    {
        return getcwd();
    }

    public function getConfiguredPackage(): PackageInterface
    {
        return $this->configuredPackage;
    }

    public function getConfiguredPackagePath(): string
    {
        if (null === $this->configuredPackagePath) {
            $installationManager = $this->composer->getInstallationManager();
            $this->configuredPackagePath = $installationManager->getInstallPath($this->configuredPackage);
        }

        return $this->configuredPackagePath;
    }

    public function getVendorDir(): string
    {
        return $this->composer->getConfig()->get('vendor-dir', Config::RELATIVE_PATHS);
    }
}
