<?php
/**
 * Http application
 *
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

use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Event;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Http implements \Magento\Framework\AppInterface
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var AreaList
     */
    protected $_areaList;

    /**
     * @var Request\Http
     */
    protected $_request;

    /**
     * @var ConfigLoader
     */
    protected $_configLoader;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var Response\Http
     */
    protected $_response;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param Event\Manager $eventManager
     * @param AreaList $areaList
     * @param RequestHttp $request
     * @param ResponseHttp $response
     * @param ConfigLoader $configLoader
     * @param State $state
     * @param Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        Event\Manager $eventManager,
        AreaList $areaList,
        RequestHttp $request,
        ResponseHttp $response,
        ConfigLoader $configLoader,
        State $state,
        Filesystem $filesystem
    ) {
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_areaList = $areaList;
        $this->_request = $request;
        $this->_response = $response;
        $this->_configLoader = $configLoader;
        $this->_state = $state;
        $this->_filesystem = $filesystem;
    }

    /**
     * Run application
     *
     * @return ResponseInterface
     */
    public function launch()
    {
        try {
            $areaCode = $this->_areaList->getCodeByFrontName($this->_request->getFrontName());
            $this->_state->setAreaCode($areaCode);
            $this->_objectManager->configure($this->_configLoader->load($areaCode));
            $this->_response = $this->_objectManager->get('Magento\Framework\App\FrontControllerInterface')
                ->dispatch($this->_request);
            // This event gives possibility to launch something before sending output (allow cookie setting)
            $eventParams = array('request' => $this->_request, 'response' => $this->_response);
            $this->_eventManager->dispatch('controller_front_send_response_before', $eventParams);
        } catch (\Exception $exception) {
            $message = $exception->getMessage() . "\n";
            try {
                if ($this->_state->getMode() == State::MODE_DEVELOPER) {
                    $message .= '<pre>';
                    $message .= $exception->getMessage() . "\n\n";
                    $message .= $exception->getTraceAsString();
                    $message .= '</pre>';
                } else {
                    $reportData = array($exception->getMessage(), $exception->getTraceAsString());
                    // retrieve server data
                    if (isset($_SERVER)) {
                        if (isset($_SERVER['REQUEST_URI'])) {
                            $reportData['url'] = $_SERVER['REQUEST_URI'];
                        }
                        if (isset($_SERVER['SCRIPT_NAME'])) {
                            $reportData['script_name'] = $_SERVER['SCRIPT_NAME'];
                        }
                    }
                    require_once $this->_filesystem->getPath(Filesystem::PUB_DIR) . '/errors/report.php';
                    $processor = new \Magento\Framework\Error\Processor($this->_response);
                    $processor->saveReport($reportData);
                    $this->_response = $processor->processReport();
                }
            } catch (\Exception $exception) {
                $message .= "Unknown error happened.";
            }
            $this->_response->setHttpResponseCode(500);
            $this->_response->setBody($message);
        }
        return $this->_response;
    }
}
