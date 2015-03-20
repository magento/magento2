<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Webdev\App;

use Magento\Framework\App;
use Magento\Framework\App\State;
use Magento\Framework\AppInterface;
use Magento\Framework\Filesystem;
use Magento\Tools\Webdev\CliParams;
use Magento\Tools\View\Deployer\Log;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\App\Console\Response;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\View\Asset\SourceFileGeneratorPool;
use Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class FileAssembler
 *
 * @package Magento\Tools\Di\App
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileAssembler implements AppInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var CliParams
     */
    private $params;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var ConfigLoader
     */
    private $configLoader;

    /**
     * @var State
     */
    private $state;

    /**
     * @var \Magento\Framework\Less\FileGenerator
     */
    private $sourceFileGeneratorPool;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    private $assetSource;

    /**
     * @var \Magento\Tools\View\Deployer\Log
     */
    private $logger;

    /**
     * @var ChainFactoryInterface
     */
    private $chainFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Response $response
     * @param CliParams $params
     * @param Repository $assetRepo
     * @param ConfigLoader $configLoader
     * @param State $state
     * @param Source $assetSource
     * @param \Magento\Framework\View\Asset\SourceFileGeneratorPool $sourceFileGeneratorPoll
     * @param \Magento\Tools\View\Deployer\Log $logger
     * @param ChainFactoryInterface $chainFactory
     * @param Filesystem $filesystem
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Response $response,
        CliParams $params,
        Repository $assetRepo,
        ConfigLoader $configLoader,
        State $state,
        Source $assetSource,
        SourceFileGeneratorPool $sourceFileGeneratorPoll,
        Log $logger,
        ChainFactoryInterface $chainFactory,
        Filesystem $filesystem
    ) {
        $this->response = $response;
        $this->params = $params;
        $this->state = $state;
        $this->objectManager = $objectManager;
        $this->configLoader = $configLoader;
        $this->assetRepo = $assetRepo;
        $this->sourceFileGeneratorPool = $sourceFileGeneratorPoll;
        $this->assetSource = $assetSource;
        $this->logger = $logger;
        $this->chainFactory = $chainFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Launch application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        $this->state->setAreaCode($this->params->getArea());
        $this->objectManager->configure($this->configLoader->load($this->params->getArea()));

        $sourceFileGenerator = $this->sourceFileGeneratorPool->create($this->params->getExt());

        foreach ($this->params->getFiles() as $file) {
            $file .= '.' . $this->params->getExt();

            $this->logger->logMessage("Gathering {$file} sources.");

            $asset = $this->assetRepo->createAsset(
                $file,
                [
                    'area' => $this->params->getArea(),
                    'theme' => $this->params->getTheme(),
                    'locale' => $this->params->getLocale(),
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

            $this->logger->logMessage("Done");
        }

        $this->response->setCode(Response::SUCCESS);

        return $this->response;
    }

    /**
     * Ability to handle exceptions that may have occurred during bootstrap and launch
     *
     * Return values:
     * - true: exception has been handled, no additional action is needed
     * - false: exception has not been handled - pass the control to Bootstrap
     *
     * @param App\Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
