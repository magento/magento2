<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\View\Asset\SourceFileGeneratorPool;
use Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class CssDeployCommand
 */
class CssDeployCommand extends Command
{
    /**
     * Type argument
     */
    const TYPE_ARGUMENT = 'type';

    const LOCALE_ARGUMENT = 'locale';

    const AREA_ARGUMENT = 'area';

    const THEME_ARGUMENT = 'theme';

    const FILES_ARGUMENT = 'files';

    /**
     * Inject dependencies
     *
     * @param ObjectManagerInterface $objectManager
     * @param Repository $assetRepo
     * @param ConfigLoader $configLoader
     * @param State $state
     * @param Source $assetSource
     * @param \Magento\Framework\View\Asset\SourceFileGeneratorPool $sourceFileGeneratorPoll
     * @param ChainFactoryInterface $chainFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $assetRepo,
        ConfigLoader $configLoader,
        State $state,
        Source $assetSource,
        SourceFileGeneratorPool $sourceFileGeneratorPoll,
        ChainFactoryInterface $chainFactory,
        Filesystem $filesystem
    ) {
        $this->state = $state;
        $this->objectManager = $objectManager;
        $this->configLoader = $configLoader;
        $this->assetRepo = $assetRepo;
        $this->sourceFileGeneratorPool = $sourceFileGeneratorPoll;
        $this->assetSource = $assetSource;
        $this->chainFactory = $chainFactory;
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dev:css:deploy')
            ->setDescription('Collects, processes and publishes source files like LESS or SASS')
            ->setDefinition([
                new InputArgument(
                    self::TYPE_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Type of dynamic stylesheet language: [less|sass]'
                ),
                new InputArgument(
                    self::LOCALE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Locale',
                    'en_US'
                ),
                new InputArgument(
                    self::AREA_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Area, one of [frontend|adminhtml|doc]',
                    'frontend'
                ),
                new InputArgument(
                    self::THEME_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Theme in format Vendor/theme',
                    'Magento/blank'
                ),
                new InputArgument(
                    self::FILES_ARGUMENT,
                    InputArgument::IS_ARRAY,
                    'Files to pre-process (accept more than one file type as comma-separate values)',
                    ['css/styles-m']
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode($input->getArgument(self::AREA_ARGUMENT));
        $this->objectManager->configure($this->configLoader->load($input->getArgument(self::AREA_ARGUMENT)));

        $sourceFileGenerator = $this->sourceFileGeneratorPool->create($input->getArgument(self::TYPE_ARGUMENT));

        foreach ($input->getArgument(self::FILES_ARGUMENT) as $file) {
            $file .= '.' . $input->getArgument(self::TYPE_ARGUMENT);

            $output->writeln("Gathering {$file} sources.");

            $asset = $this->assetRepo->createAsset(
                $file,
                [
                    'area' => $input->getArgument(self::AREA_ARGUMENT),
                    'theme' => $input->getArgument(self::THEME_ARGUMENT),
                    'locale' => $input->getArgument(self::LOCALE_ARGUMENT),
                ]
            );

            $sourceFile = $this->assetSource->findSource($asset);
            $content = \file_get_contents($sourceFile);

            $chain = $this->chainFactory->create(
                [
                    'asset'           => $asset,
                    'origContent'     => $content,
                    'origContentType' => $asset->getContentType()
                ]
            );

            $processedCoreFile = $sourceFileGenerator->generateFileTree($chain);

            $targetDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
            $rootDir = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
            $source = $rootDir->getRelativePath($processedCoreFile);
            $destination = $asset->getPath();
            $rootDir->copyFile($source, $destination, $targetDir);

            $output->writeln("Done");
        }
    }
}
