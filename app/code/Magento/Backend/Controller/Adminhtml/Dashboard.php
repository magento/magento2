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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Dashboard admin controller
 *
 * @category   Magento
 * @package    Magento_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Controller\Adminhtml;

class Dashboard extends \Magento\Backend\App\Action
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(\Magento\Backend\App\Action\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Dashboard'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backend::dashboard');
        $this->_addBreadcrumb(__('Dashboard'), __('Dashboard'));
        $this->_view->renderLayout();
    }

    /**
     * Gets most viewed products list
     *
     * @return void
     */
    public function productsViewedAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Gets latest customers list
     *
     * @return void
     */
    public function customersNewestAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Gets the list of most active customers
     *
     * @return void
     */
    public function customersMostAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function ajaxBlockAction()
    {
        $output = '';
        $blockTab = $this->getRequest()->getParam('block');
        $blockClassSuffix = str_replace(
            ' ',
            \Magento\Autoload\IncludePath::NS_SEPARATOR,
            ucwords(str_replace('_', ' ', $blockTab))
        );
        if (in_array($blockTab, array('tab_orders', 'tab_amounts', 'totals'))) {
            $output = $this->_view->getLayout()->createBlock(
                'Magento\\Backend\\Block\\Dashboard\\' . $blockClassSuffix
            )->toHtml();
        }
        $this->getResponse()->setBody($output);
        return;
    }

    /**
     * Forward request for a graph image to the web-service
     *
     * This is done in order to include the image to a HTTPS-page regardless of web-service settings
     *
     * @return void
     */
    public function tunnelAction()
    {
        $error = __('invalid request');
        $httpCode = 400;
        $gaData = $this->_request->getParam('ga');
        $gaHash = $this->_request->getParam('h');
        if ($gaData && $gaHash) {
            /** @var $helper \Magento\Backend\Helper\Dashboard\Data */
            $helper = $this->_objectManager->get('Magento\Backend\Helper\Dashboard\Data');
            $newHash = $helper->getChartDataHash($gaData);
            if ($newHash == $gaHash) {
                $params = json_decode(base64_decode(urldecode($gaData)), true);
                if ($params) {
                    try {
                        /** @var $httpClient \Magento\HTTP\ZendClient */
                        $httpClient = $this->_objectManager->create('Magento\HTTP\ZendClient');
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

                        $this->_response->setHeader(
                            'Content-type',
                            $headers['Content-type']
                        )->setBody(
                            $response->getBody()
                        );
                        return;
                    } catch (\Exception $e) {
                        $this->_objectManager->get('Magento\Logger')->logException($e);
                        $error = __('see error log for details');
                        $httpCode = 503;
                    }
                }
            }
        }
        $this->_response->setBody(
            __('Service unavailable: %1', $error)
        )->setHeader(
            'Content-Type',
            'text/plain; charset=UTF-8'
        )->setHttpResponseCode(
            $httpCode
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Adminhtml::dashboard');
    }
}
