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
use Magento\Tools\Webdev\CliParams;
use Magento\Tools\View\Deployer\Log;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\Less\FileGenerator;
use Magento\Framework\App\Console\Response;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\App\ObjectManager\ConfigLoader;

/**
 *
 * Class Compiler
 * @package Magento\Tools\Di\App
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Lesser implements AppInterface
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
    private $fileGenerator;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    private $assetSource;

    /**
     * @var \Magento\Tools\View\Deployer\Log
     */
    private $logger;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Response $response
     * @param CliParams $params
     * @param Repository $assetRepo
     * @param ConfigLoader $configLoader
     * @param State $state
     * @param Source $assetSource
     * @param FileGenerator $fileGenerator
     * @param \Magento\Tools\View\Deployer\Log $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Response $response,
        CliParams $params,
        Repository $assetRepo,
        ConfigLoader $configLoader,
        State $state,
        Source $assetSource,
        FileGenerator $fileGenerator,
        Log $logger
    ) {
        $this->response = $response;
        $this->params = $params;
        $this->state = $state;
        $this->objectManager = $objectManager;
        $this->configLoader = $configLoader;
        $this->assetRepo = $assetRepo;
        $this->fileGenerator = $fileGenerator;
        $this->assetSource = $assetSource;
        $this->logger = $logger;
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

        foreach ($this->params->getFiles() as $file) {
            $file .= '.less';

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
            $chain = new Chain($asset, $content, 'less');
            $this->fileGenerator->generateLessFileTree($chain);

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
