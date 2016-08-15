<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Tests;

abstract class ConfiguratorTestCase extends TestCase
{
    const CONFIGURATOR = null;

    protected $configurator;

    protected function setUp()
    {
        parent::setUp();

        $class = static::CONFIGURATOR;
        $this->configurator = new $class();
    }
}
