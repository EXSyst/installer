<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Configurator;

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
     */
    public function configure(Project $project)
    {
        $bundle = $this->getBundle($project);
        if (null === $bundle) {
            $io->write('<error>No bundle found.</error>');

            return;
        }

        $io = $project->getIO();
        $kernelFinder = new KernelFinder();
        $kernel = null;
        foreach ($kernelFinder->findKernels($project) as $kernel) {
            if (!$io->askConfirmation(sprintf('<info>Add the bundle "%s" to your kernel "%s" ?</info> [<comment>yes</comment>]: ', $bundle, $kernel))) {
                continue;
            }

            $kernelManipulator = new KernelManipulator($kernel);

            try {
                $kernelManipulator->addBundle($bundle);
                $message = 'has been';
            } catch (\RuntimeException $e) {
                $message = 'was already';
            }

            $io->write(sprintf('<comment>%s</comment> %s registered in <comment>%s</comment>.', $bundle, $message, $kernel));
        }

        if (null === $kernel) {
            $io->write('<error>No kernel found.</error>');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Project $project)
    {
        $package = $project->getInstalledPackage();
        if ('symfony-bundle' !== $package->getType()) {
            return false;
        }

        // not a symfony project
        $rootPackage = $project->getRootPackage();
        $extra = $rootPackage->getExtra();
        if (!isset($extra['symfony-app-dir'])) {
            return false;
        }

        return true;
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
