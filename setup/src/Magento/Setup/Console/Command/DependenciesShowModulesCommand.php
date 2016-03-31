<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\Dependency\ServiceLocator;

/**
 * Command for showing number of dependencies between modules
 */
class DependenciesShowModulesCommand extends AbstractDependenciesCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Shows number of dependencies between modules')
            ->setName('info:dependencies:show-modules');
        parent::configure();
    }

    /**
     * Return default output filename for modules dependencies report
     *
     * @return string
     */
    protected function getDefaultOutputFilename()
    {
        return 'modules-dependencies.csv';
    }

    /**
     * Build circular dependencies between modules report
     *
     * @param string $outputPath
     * @return void
     */
    protected function buildReport($outputPath)
    {
        $filesForParse = Files::init()->getComposerFiles(ComponentRegistrar::MODULE, false);

        ServiceLocator::getDependenciesReportBuilder()->build(
            [
                'parse' => ['files_for_parse' => $filesForParse],
                'write' => ['report_filename' => $outputPath],
            ]
        );
    }
}
