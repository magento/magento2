<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request;
use Magento\Framework\App\Response;
use Magento\Framework\App;

/**
 * Entry point for retrieving static resources like JS, CSS, images by requested public path
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StaticResource implements \Magento\Framework\AppInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var \Magento\Framework\App\Response\FileInterface
     */
    private $response;

    /**
     * @var Request\Http
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
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectManager\ConfigLoader
     */
    private $configLoader;

    /**
     * @param State $state
     * @param Response\FileInterface $response
     * @param Request\Http $request
     * @param \Magento\Framework\App\View\Asset\Publisher $publisher
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param ObjectManager\ConfigLoader $configLoader
     */
    public function __construct(
        State $state,
        Response\FileInterface $response,
        Request\Http $request,
        View\Asset\Publisher $publisher,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\ObjectManager $objectManager,
        ObjectManager\ConfigLoader $configLoader
    ) {
        $this->state = $state;
        $this->response = $response;
        $this->request = $request;
        $this->publisher = $publisher;
        $this->assetRepo = $assetRepo;
        $this->moduleList = $moduleList;
        $this->objectManager = $objectManager;
        $this->configLoader = $configLoader;
    }

    /**
     * Finds requested resource and provides it to the client
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function launch()
    {
        $appMode = $this->state->getMode();
        if ($appMode == \Magento\Framework\App\State::MODE_PRODUCTION) {
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
     * {@inheritdoc}
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        $this->response->setHttpResponseCode(404);
        $this->response->setHeader('Content-Type', 'text/plain');
        if ($bootstrap->isDeveloperMode()) {
            $this->response->setBody($exception->getMessage() . "\n" . $exception->getTraceAsString());
        }
        $this->response->sendResponse();
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
        if (count($parts) < 5) {
            throw new \InvalidArgumentException("Requested path '$path' is wrong.");
        }
        $result = array();
        $result['area'] = $parts[0];
        $result['theme'] = $parts[1] . '/' . $parts[2];
        $result['locale'] = $parts[3];
        if (count($parts) >= 6 && $this->isModule($parts[4])) {
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
     * Check if active module 'name' exists
     *
     * @param string $name
     * @return bool
     */
    protected function isModule($name)
    {
        return null !== $this->moduleList->getModule($name);
    }
}
