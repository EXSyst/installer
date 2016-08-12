<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Util;

use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
class ClassFinder
{
    /**
     * @param Finder $finder
     * @param string $parent A class or interface name
     *
     * @return string|null
     */
    public function findClass(Finder $finder, $parent)
    {
        foreach ($this->findClasses($finder, $parent) as $class) {
            return $class;
        }
    }

    /**
     * @param Finder $finder
     * @param string $parent A class or interface name
     *
     * @return array|\Traversable
     */
    public function findClasses(Finder $finder, $parent)
    {
        $finder->files();
        foreach ($finder as $file) {
            $sourceFile = $file->getRealpath();
            if (!preg_match('(^phar:)i', $sourceFile)) {
                $sourceFile = realpath($sourceFile);
            }

            foreach ($this->getClassesIn($sourceFile) as $class) {
                if (is_subclass_of($class, $parent)) {
                    yield $class;
                }
            }
        }

        return [];
    }

    /**
     * @param string|string[] $files
     *
     * @return string[]
     */
    public function getClassesIn($files)
    {
        $files = array_flip((array) $files);
        foreach ($files as $file => $v) {
            require_once $file;
        }

        $classes = [];
        $declared = get_declared_classes();
        foreach ($declared as $className) {
            $reflectionClass = new \ReflectionClass($className);
            $sourceFile = $reflectionClass->getFileName();
            if ($reflectionClass->isAbstract()) {
                continue;
            }
            if (isset($files[$sourceFile])) {
                $classes[$className] = true;
            }
        }

        return array_keys($classes);
    }
}
