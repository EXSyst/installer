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

use EXSyst\Installer\Symfony\Config\FileLocator;
use EXSyst\Installer\Tests\TestCase;

class FileLocatorTest extends TestCase
{
    private static $vendorDir = __DIR__.'/../../../vendor';
    private $locator;

    public function testLocate()
    {
        $this->assertPathEquals(self::$vendorDir.'/dunglas/action-bundle/LICENSE', $this->locator->locate('@DunglasActionBundle/LICENSE'));
    }

    public function testLocateWithCurrentDir()
    {
        $this->assertPathEquals(self::$fixturesDir.'/DunglasActionBundle/foo.txt', $this->locator->locate('@DunglasActionBundle/Resources/foo.txt', self::$fixturesDir));
    }

    protected function setUp()
    {
        parent::setUp();

        list($project) = $this->getProject('dunglas/action-bundle');
        $this->locator = new FileLocator($project);
    }

    private function assertPathEquals(string $expected, string $actual)
    {
        $expected = realpath($expected);
        $this->assertEquals($expected, $actual);
    }
}
