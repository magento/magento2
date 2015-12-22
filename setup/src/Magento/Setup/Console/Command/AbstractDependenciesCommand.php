<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class for dependency report commands
 */
abstract class AbstractDependenciesCommand extends Command
{
    /**
     * Input key for directory option
     */
    const INPUT_KEY_DIRECTORY = 'directory';

    /**
     * Input key for output path of report file
     */
    const INPUT_KEY_OUTPUT = 'output';

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManager = $objectManagerProvider->get();
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDefinition(
            [
                new InputOption(
                    self::INPUT_KEY_OUTPUT,
                    'o',
                    InputOption::VALUE_REQUIRED,
                    'Report filename',
                    $this->getDefaultOutputFilename()
                )
            ]
        );
        parent::configure();
    }

    /**
     * Build dependencies report
     *
     * @param string $outputPath
     * @return void
     */
    abstract protected function buildReport($outputPath);

    /**
     * Get the default output report filename
     *
     * @return string
     */
    abstract protected function getDefaultOutputFilename();

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var \Magento\Framework\Component\ComponentRegistrar $componentRegistrar */
            $componentRegistrar = $this->objectManager->get('Magento\Framework\Component\ComponentRegistrar');
            /** @var \Magento\Framework\Component\DirSearch $dirSearch */
            $dirSearch = $this->objectManager->get('Magento\Framework\Component\DirSearch');
            /** @var \Magento\Framework\View\Design\Theme\ThemePackageList $themePackageList */
            $themePackageList = $this->objectManager->get('Magento\Framework\View\Design\Theme\ThemePackageList');
            Files::setInstance(new Files($componentRegistrar, $dirSearch, $themePackageList));
            $this->buildReport($input->getOption(self::INPUT_KEY_OUTPUT));
            $output->writeln('<info>Report successfully processed.</info>');
        } catch (\Exception $e) {
            $output->writeln(
                '<error>Please check the path you provided. Dependencies report generator failed with error: ' .
                $e->getMessage() . '</error>'
            );
        }
    }
}
