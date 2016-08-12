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
     * Should not be instantiated in your code.
     */
    private function __construct(Composer $composer, IOInterface $io, PackageInterface $installedPackage)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->installedPackage = $installedPackage;
    }

    /**
     * @internal
     */
    public static function fromPackageEvent(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();

        return new self($event->getComposer(), $event->getIO(), $installedPackage);
    }

    /**
     * @return IOInterface
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * @return RootPackageInterface
     */
    public function getRootPackage()
    {
        return $this->composer->getPackage();
    }

    /**
     * @return string
     */
    public function getRootPackagePath()
    {
        return getcwd();
    }

    /**
     * @return PackageInterface
     */
    public function getInstalledPackage()
    {
        return $this->installedPackage;
    }

    /**
     * @return string
     */
    public function getInstalledPackagePath()
    {
        if (null === $this->installedPackagePath) {
            $installationManager = $this->composer->getInstallationManager();
            $this->installedPackagePath = $installationManager->getInstallPath($this->installedPackage);
        }

        return $this->installedPackagePath;
    }

    public function getVendorDir()
    {
        return $this->composer->getConfig()->get('vendor-dir', Config::RELATIVE_PATHS);
    }
}
