<?php
/**
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
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result;

class Tunnel extends \Magento\Backend\Controller\Adminhtml\Dashboard
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
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
     * @return  \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $error = __('invalid request');
        $httpCode = 400;
        $gaData = $this->_request->getParam('ga');
        $gaHash = $this->_request->getParam('h');
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        if ($gaData && $gaHash) {
            /** @var $helper \Magento\Backend\Helper\Dashboard\Data */
            $helper = $this->_objectManager->get('Magento\Backend\Helper\Dashboard\Data');
            $newHash = $helper->getChartDataHash($gaData);
            if ($newHash == $gaHash) {
                $params = json_decode(base64_decode(urldecode($gaData)), true);
                if ($params) {
                    try {
                        /** @var $httpClient \Magento\Framework\HTTP\ZendClient */
                        $httpClient = $this->_objectManager->create('Magento\Framework\HTTP\ZendClient');
                        $response = $httpClient->setUri(
                            \Magento\Backend\Block\Dashboard\Graph::API_URL
                        )->setParameterGet(
                            $params
                        )->setConfig(
                            array('timeout' => 5)
                        )->request(
                            'GET'
                        );

                        $headers = $response->getHeaders();

                        $resultRaw->setHeader('Content-type', $headers['Content-type'])
                            ->setContents($response->getBody());
                        return $resultRaw;
                    } catch (\Exception $e) {
                        $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
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
