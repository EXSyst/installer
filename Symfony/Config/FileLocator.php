<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Symfony\Config;

use EXSyst\Installer\Project;
use EXSyst\Installer\Util\ClassFinder;
use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * FileLocator using the class finder to locate bundle resources.
 *
 * @author Guilhem N. <egetick@gmail.com>
 *
 * @internal
 */
class FileLocator extends BaseFileLocator
{
    private $project;

    public function __construct(Project $project)
    {
        parent::__construct();
        $this->project = $project;
    }

    /**
     * Extracted from {@link Kernel::locateResource()}.
     *
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if (isset($file[0]) && '@' !== $file[0]) {
            return parent::locate($file, $currentPath, $first);
        }

        $name = $file;
        $dir = $currentPath;

        $bundleName = substr($name, 1);
        $path = '';
        if (false !== strpos($bundleName, '/')) {
            list($bundleName, $path) = explode('/', $bundleName, 2);
        }

        $isResource = 0 === strpos($path, 'Resources') && null !== $dir;
        $overridePath = substr($path, 9);
        $resourceBundle = null;
        $bundles = $this->getBundle($bundleName, false);
        $files = array();

        foreach ($bundles as $bundle) {
            if ($isResource && file_exists($file = $dir.'/'.$bundle->getName().$overridePath)) {
                if (null !== $resourceBundle) {
                    throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived bundle. Create a "%s" file to override the bundle resource.',
                        $file,
                        $resourceBundle,
                        $dir.'/'.$bundles[0]->getName().$overridePath
                    ));
                }

                if ($first) {
                    return $file;
                }
                $files[] = $file;
            }

            if (file_exists($file = $bundle->getPath().'/'.$path)) {
                if ($first && !$isResource) {
                    return $file;
                }
                $files[] = $file;
                $resourceBundle = $bundle->getName();
            }
        }

        if (count($files) > 0) {
            return $first && $isResource ? $files[0] : $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }

    /**
     * @return array|\Traversable
     */
    private function getBundle($bundle)
    {
        $finder = new Finder();
        $finder
            ->in($this->project->getRootPackagePath())
            ->name('*Bundle.php');
        $classFinder = new ClassFinder();
        foreach ($classFinder->findClassesFromShortName($bundle, $finder, BundleInterface::class) as $class) {
            yield new Bundle($class, $bundle);
        }

        return [];
    }
}

class Bundle
{
    private $class;
    private $name;

    public function __construct(string $class, string $name) {
        $this->class = $class;
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        $reflectionClass = new \ReflectionClass($this->class);

        return dirname($reflectionClass->getFileName());
    }
}
