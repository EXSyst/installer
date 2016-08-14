<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Tests\Symfony\Configurator;

use EXSyst\Installer\Tests\AbstractConfiguratorTest;
use TestKernel;

abstract class AbstractBundleConfiguratorTest extends AbstractConfiguratorTest
{
    protected function assertBundleIsRegistered(string $bundle)
    {
        $kernel = file_get_contents(static::$fixturesDir.'/TestKernel.php');

        $this->assertContains($bundle, $kernel);
    }
}
