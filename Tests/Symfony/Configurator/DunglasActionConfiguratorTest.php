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

use EXSyst\Installer\Symfony\Configurator\DunglasActionConfigurator;

class DunglasActionConfiguratorTest extends AbstractBundleConfiguratorTest
{
    const CONFIGURATOR = DunglasActionConfigurator::class;

    public function testSupports()
    {
        list($project, $output) = $this->getProject('dunglas/action-bundle');
        $this->assertTrue($this->configurator->supports($project));
    }
}
