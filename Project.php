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
    private $installedPackage;
    private $installedPackagePath;

    /**
     * @internal
     */
    public function __construct(Composer $composer, IOInterface $io, PackageInterface $installedPackage)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->installedPackage = $installedPackage;
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

    public function getInstalledPackage(): PackageInterface
    {
        return $this->installedPackage;
    }

    public function getInstalledPackagePath(): string
    {
        if (null === $this->installedPackagePath) {
            $installationManager = $this->composer->getInstallationManager();
            $this->installedPackagePath = $installationManager->getInstallPath($this->installedPackage);
        }

        return $this->installedPackagePath;
    }

    public function getVendorDir(): string
    {
        return $this->composer->getConfig()->get('vendor-dir', Config::RELATIVE_PATHS);
    }
}
