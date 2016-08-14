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
use EXSyst\Installer\Symfony\KernelFinder;
use EXSyst\Installer\Symfony\KernelManipulator;
use EXSyst\Installer\Util\ClassFinder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class BundleConfigurator implements ConfiguratorInterface
{
    /**
     * {@inheritdoc}
     *
     * @return bool true if the bundle is registered in the kernel, false otherwise.
     */
    public function configure(Project $project): bool
    {
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
        $package = $project->getInstalledPackage();
        if ('symfony-bundle' !== $package->getType()) {
            return false;
        }

        // symfony project if a kernel can be found
        $kernelFinder = new KernelFinder();

        return null !== $kernelFinder->findKernel($project);
    }

    /**
     * @return string|null the bundle class
     */
    protected function getBundle(Project $project)
    {
        $finder = new Finder();
        $finder
            ->in($project->getInstalledPackagePath())
            ->name('*Bundle.php');
        $classFinder = new ClassFinder();

        return $classFinder->findClass($finder, BundleInterface::class);
    }
}
