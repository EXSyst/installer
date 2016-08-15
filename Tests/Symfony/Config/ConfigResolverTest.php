<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Tests\Symfony\Config;

use Dunglas\ActionBundle\DependencyInjection\DunglasActionExtension;
use EXSyst\Installer\Symfony\Config\ConfigResolver;
use EXSyst\Installer\Tests\TestCase;

class ConfigResolverTest extends TestCase
{
    private $resolver;

    public function testResolve()
    {
        $extension = new DunglasActionExtension();
        list($project) = $this->getProject('dunglas/action-bundle');

        $this->assertEquals(['%kernel.root_dir%/../bar/', 'foo/'], $this->resolver->getConfig(self::$fixturesDir.'/config/config.yml', $extension, $project)['directories']);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->resolver = new ConfigResolver($project);
    }
}
