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

use Composer\Composer;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Composer\IO\IOInterface;
use EXSyst\Installer\Project;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected static $fixturesDir = __DIR__.'/Fixtures';

    protected function setUp()
    {
        file_put_contents(static::$fixturesDir.'/TestKernel.php', file_get_contents(static::$fixturesDir.'/TestKernel.php.dist'));
    }

    protected function getProject(string $installedPackage, string $interaction = ''): array
    {
        chdir(__DIR__.'/..');

        $input = new ArrayInput([]);
        $output = new StreamOutput(fopen('php://memory', 'w', false));

        $io = new ConsoleIO($input, $output, $this->getHelperSet($interaction));
        $composer = $this->getComposer($io);
        $installedPackage = $composer->getRepositoryManager()->getLocalRepository()->findPackage($installedPackage, '*');

        return [new Project($composer, $io, $installedPackage), $output];
    }

    /**
     * Gets the display returned by the last execution of the command.
     */
    protected function getDisplay(StreamOutput $output)
    {
        rewind($output->getStream());
        $display = stream_get_contents($output->getStream());
        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }

    /**
     * Creates composer from the package config.
     */
    private function getComposer(IOInterface $io): Composer
    {
        $factory = new Factory();

        // Disable plugins and full loading
        return $factory->createComposer($io, null, true, null, false);
    }

    private function getHelperSet(string $interaction)
    {
        $questionHelper = new QuestionHelper();
        $questionHelper->setInputStream($this->getInteractionStream($interaction));

        return new HelperSet([
            new FormatterHelper(),
            new DebugFormatterHelper(),
            new ProcessHelper(),
            $questionHelper,
        ]);
    }

    private function getInteractionStream($interaction)
    {
        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $interaction);
        rewind($stream);

        return $stream;
    }
}
