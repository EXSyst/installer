<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Configurator;

use EXSyst\Installer\Project;

interface ConfiguratorInterface
{
    /**
     * Configure a package.
     */
    public function configure(Project $project);
    public function supports(Project $project): bool;
}
