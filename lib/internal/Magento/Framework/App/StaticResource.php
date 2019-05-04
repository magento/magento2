<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Psr\Log\LoggerInterface;
use Magento\Framework\Debug;

/**
 * Entry point for retrieving static resources like JS, CSS, images by requested public path
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StaticResource implements \Magento\Framework\AppInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Framework\App\Response\FileInterface
     */
    private $response;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\View\Asset\Publisher
     */
    private $publisher;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\ObjectManager\ConfigLoaderInterface
     */
    private $configLoader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param State $state
     * @param Response\FileInterface $response
     * @param Request\Http $request
     * @param View\Asset\Publisher $publisher
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigLoaderInterface $configLoader
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        State $state,
        Response\FileInterface $response,
        Request\Http $request,
        View\Asset\Publisher $publisher,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ConfigLoaderInterface $configLoader,
        DeploymentConfig $deploymentConfig = null
    ) {
        $this->state = $state;
        $this->response = $response;
        $this->request = $request;
        $this->publisher = $publisher;
        $this->assetRepo = $assetRepo;
        $this->moduleList = $moduleList;
        $this->objectManager = $objectManager;
        $this->configLoader = $configLoader;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
    }

    /**
     * Finds requested resource and provides it to the client
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function launch()
    {
        // disabling profiling when retrieving static resource
        \Magento\Framework\Profiler::reset();
        $appMode = $this->state->getMode();
        if ($appMode == \Magento\Framework\App\State::MODE_PRODUCTION
            && !$this->deploymentConfig->getConfigData(
                ConfigOptionsListConstants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION
            )
        ) {
            $this->response->setHttpResponseCode(404);
        } else {
            $path = $this->request->get('resource');
            $params = $this->parsePath($path);
            $this->state->setAreaCode($params['area']);
            $this->objectManager->configure($this->configLoader->load($params['area']));
            $file = $params['file'];
            unset($params['file']);
            $asset = $this->assetRepo->createAsset($file, $params);
            $this->response->setFilePath($asset->getSourceFile());
            $this->publisher->publish($asset);
        }
        return $this->response;
    }

    /**
     * @inheritdoc
     */
    public function catchException(Bootstrap $bootstrap, \Exception $exception)
    {
        $this->getLogger()->critical($exception->getMessage());
        if ($bootstrap->isDeveloperMode()) {
            $this->response->setHttpResponseCode(404);
            $this->response->setHeader('Content-Type', 'text/plain');
            $this->response->setBody(
                $exception->getMessage() . "\n" .
                Debug::trace(
                    $exception->getTrace(),
                    true,
                    true,
                    (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
                )
            );
            $this->response->sendResponse();
        } else {
            require $this->getFilesystem()->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('errors/404.php');
        }
        return true;
    }

    /**
     * Parse path to identify parts needed for searching original file
     *
     * @param string $path
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function parsePath($path)
    {
        $path = ltrim($path, '/');
        $parts = explode('/', $path, 6);
        if (count($parts) < 5 || preg_match('/\.\.(\\\|\/)/', $path)) {
            //Checking that path contains all required parts and is not above static folder.
            throw new \InvalidArgumentException("Requested path '$path' is wrong.");
        }

        $result = [];
        $result['area'] = $parts[0];
        $result['theme'] = $parts[1] . '/' . $parts[2];
        $result['locale'] = $parts[3];
        if (count($parts) >= 6 && $this->moduleList->has($parts[4])) {
            $result['module'] = $parts[4];
        } else {
            $result['module'] = '';
            if (isset($parts[5])) {
                $parts[5] = $parts[4] . '/' . $parts[5];
            } else {
                $parts[5] = $parts[4];
            }
        }
        $result['file'] = $parts[5];
        return $result;
    }

    /**
     * Lazyload filesystem driver
     *
     * @deprecated 100.1.0
     * @return Filesystem
     */
    private function getFilesystem()
    {
        if (!$this->filesystem) {
            $this->filesystem = $this->objectManager->get(Filesystem::class);
        }
        return $this->filesystem;
    }

    /**
     * Retrieves LoggerInterface instance
     *
     * @return LoggerInterface
     * @deprecated 100.2.0
     */
    private function getLogger()
    {
        if (!$this->logger) {
            $this->logger = $this->objectManager->get(LoggerInterface::class);
        }

        return $this->logger;
    }
}
