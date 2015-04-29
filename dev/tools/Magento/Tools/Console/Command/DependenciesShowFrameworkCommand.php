<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Console\Command;

use Magento\Framework\App\Utility\Files;
use Magento\Tools\Dependency\ServiceLocator;

/**
 * Command for showing numbers of dependencies on Magento Framework
 */
class DependenciesShowFrameworkCommand extends AbstractDependenciesCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Shows number of dependencies on Magento framework')
            ->setName('info:dependencies:show-framework');
        parent::configure();
    }

    /**
     * Build Framework dependencies report
     *
     * @return void
     */
    protected function buildReport()
    {
        $filesForParse = Files::init()->getFiles([Files::init()->getPathToSource() . '/app/code/Magento'], '*');
        $configFiles = Files::init()->getConfigFiles('module.xml', [], false);

        ServiceLocator::getFrameworkDependenciesReportBuilder()->build(
            [
                'parse' => [
                    'files_for_parse' => $filesForParse,
                    'config_files' => $configFiles,
                    'declared_namespaces' => Files::init()->getNamespaces(),
                ],
                'write' => ['report_filename' => 'framework-dependencies.csv'],
            ]
        );
    }
}
