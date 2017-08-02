<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\Dependency\ServiceLocator;

/**
 * Command for showing number of dependencies between modules
 * @since 2.0.0
 */
class DependenciesShowModulesCommand extends AbstractDependenciesCommand
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
