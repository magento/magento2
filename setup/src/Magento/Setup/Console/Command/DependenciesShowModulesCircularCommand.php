<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\Dependency\ServiceLocator;

/**
 * Command for showing number of circular dependencies between modules
 */
class DependenciesShowModulesCircularCommand extends AbstractDependenciesCommand
{
    public const NAME = 'info:dependencies:show-modules-circular';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Shows number of circular dependencies between modules')
            ->setName(self::NAME);
        parent::configure();
    }

    /**
     * Return default output filename for modules circular dependencies report
     *
     * @return string
     */
    protected function getDefaultOutputFilename()
    {
        return 'modules-circular-dependencies.csv';
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

        asort($filesForParse);
        ServiceLocator::getCircularDependenciesReportBuilder()->build(
            [
                'parse' => ['files_for_parse' => $filesForParse],
                'write' => ['report_filename' => $outputPath],
            ]
        );
    }
}
