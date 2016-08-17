<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer;

use Composer\Command\BaseCommand;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use EXSyst\Installer\Symfony\Configurator\BundleConfigurator;
use EXSyst\Installer\Symfony\Configurator\DunglasActionConfigurator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class CommandProvider implements CommandProviderCapability
{
    public function getCommands()
    {
        return [new ConfigureCommand()];
    }
}

/**
 * @internal
 */
class ConfigureCommand extends BaseCommand
{
    private $configurators;

    protected function configure()
    {
        $this
            ->setName('configure')
            ->addArgument('package', InputArgument::REQUIRED, 'The package you want to configure');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composer = $this->getComposer();
        $repository = $composer->getRepositoryManager()->getLocalRepository();
        $package = $repository->findPackage($input->getArgument('package'), '*');
        if (null === $package) {
            throw new \LogicException(sprintf('Package "%s" not found.', $input->getArgument('package')));
        }

        $io = $this->getIO();
        $project = new Project($composer, $io, $package);

        foreach ($this->getConfigurators() as $configurator) {
            if ($configurator->supports($project)) {
                $configurator->configure($project);
                $io->write('');

                return;
            }
        }

        $io->write([sprintf('No configurator found for "%s".', $package->getName()), '']);
    }

    private function getConfigurators(): array
    {
        if (null === $this->configurators) {
            $this->configurators = [
                new DunglasActionConfigurator(),
                new BundleConfigurator(),
            ];
        }

        return $this->configurators;
    }
}
