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

use Dunglas\ActionBundle\DunglasActionBundle;
use EXSyst\Installer\Symfony\Configurator\BundleConfigurator;

class BundleConfiguratorTest extends AbstractBundleConfiguratorTest
{
    const CONFIGURATOR = BundleConfigurator::class;

    /**
     * @dataProvider kernelRegistrationInputProvider
     */
    public function testKernelRegistration($input)
    {
        list($project, $output) = $this->getProject('dunglas/action-bundle', $input);
        $this->configurator->configure($project);
        $this->configurator->configure($project);

        $this->assertBundleIsRegistered(DunglasActionBundle::class);
        $this->assertStringEqualsFile(static::$fixturesDir.'/output/bundle_registration.txt', $this->getDisplay($output));
    }

    public function kernelRegistrationInputProvider()
    {
        return [["\n\n"], ["yes\nyes\n"]];
    }

    public function testSupports()
    {
        list($project, $output) = $this->getProject('dunglas/action-bundle');
        $this->assertTrue($this->configurator->supports($project));
    }

    public function testSimpleLibrarySupport()
    {
        list($project) = $this->getProject('symfony/finder');
        $this->assertFalse($this->configurator->supports($project));
    }
}
