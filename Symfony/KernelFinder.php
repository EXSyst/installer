<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Symfony;

use EXSyst\Installer\Project;
use EXSyst\Installer\Util\ClassFinder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @internal
 */
class KernelFinder
{
    /**
     * @return \Traversable
     */
    public function findKernels(Project $project)
    {
        $vendorDir = $project->getVendorDir();

        $finder = new Finder();
        $finder
            ->in($project->getRootPackagePath())
            ->exclude($vendorDir)
            ->name('*Kernel.php');

        $classFinder = new ClassFinder();
        $kernels = $classFinder->findClasses($finder, KernelInterface::class);
        foreach ($kernels as $kernel) {
            $method = new \ReflectionMethod($kernel, 'registerBundles');
            // Ensure bundles aren't stored in a parent
            if ($kernel === $method->getDeclaringClass()->getName()) {
                yield $kernel;
            }
        }
    }
}
