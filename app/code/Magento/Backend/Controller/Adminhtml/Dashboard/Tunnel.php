<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Exception;
use Laminas\Http\Request;
use Magento\Backend\App\Action;
use Magento\Backend\Block\Dashboard\Graph;
use Magento\Backend\Controller\Adminhtml\Dashboard;
use Magento\Backend\Helper\Dashboard\Data;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\HTTP\LaminasClient;
use Psr\Log\LoggerInterface;

/**
 * Dashboard graph image tunnel
 * @deprecated dashboard graphs were migrated to dynamic chart.js solution
 * @see dashboard.chart.amounts and dashboard.chart.orders in adminhtml_dashboard_index.xml
 */
class Tunnel extends Dashboard implements HttpGetActionInterface
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Forward request for a graph image to the web-service
     *
     * This is done in order to include the image to a HTTPS-page regardless of web-service settings
     *
     * @return  Raw
     */
    public function execute()
    {
        $error = __('invalid request');
        $httpCode = 400;
        $gaData = $this->_request->getParam('ga');
        $gaHash = $this->_request->getParam('h');
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        if ($gaData && $gaHash) {
            /** @var $helper Data */
            $helper = $this->_objectManager->get(Data::class);
            $newHash = $helper->getChartDataHash($gaData);
            if (Security::compareStrings($newHash, $gaHash)) {
                $params = null;
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $paramsJson = base64_decode(urldecode($gaData));
                if ($paramsJson) {
                    $params = json_decode($paramsJson, true);
                }
                if ($params) {
                    try {
                        $httpClient = $this->_objectManager->create(LaminasClient::class);
                        $httpClient->setUri(Graph::API_URL);
                        $httpClient->setParameterGet($params);
                        $httpClient->setOptions(['timeout' => 5]);
                        $httpClient->setMethod(Request::METHOD_GET);
                        $response = $httpClient->send();
                        $headers = $response->getHeaders()->toArray();

                        $resultRaw->setHeader('Content-type', $headers['Content-type'])
                            ->setContents($response->getBody());
                        return $resultRaw;
                    } catch (Exception $e) {
                        $this->_objectManager->get(LoggerInterface::class)->critical($e);
                        $error = __('see error log for details');
                        $httpCode = 503;
                    }
                }
            }
        }
        $resultRaw->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setHttpResponseCode($httpCode)
            ->setContents(__('Service unavailable: %1', $error));
        return $resultRaw;
    }
}
