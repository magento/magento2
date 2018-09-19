<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Authorizenet\Controller\Directpost\Payment;
use Magento\Authorizenet\Helper\DataFactory;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Payment\Model\IframeConfigProvider;
use Magento\Quote\Api\CartManagementInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Place
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Place extends Payment implements HttpPostActionInterface
{
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onepageCheckout;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataFactory $dataFactory
     * @param CartManagementInterface $cartManagement
     * @param Onepage $onepageCheckout
     * @param JsonHelper $jsonHelper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataFactory $dataFactory,
        CartManagementInterface $cartManagement,
        Onepage $onepageCheckout,
        JsonHelper $jsonHelper,
        LoggerInterface $logger = null
    ) {
        $this->eventManager = $context->getEventManager();
        $this->cartManagement = $cartManagement;
        $this->onepageCheckout = $onepageCheckout;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        parent::__construct($context, $coreRegistry, $dataFactory);
    }

    /**
     * Send request to authorize.net
     *
     * @return string
     */
    public function execute()
    {
        $paymentParam = $this->getRequest()->getParam('payment');
        $controller = $this->getRequest()->getParam('controller');
        $response = $this->getResponse();

        if (isset($paymentParam['method'])) {
            $this->_getDirectPostSession()->setQuoteId($this->_getCheckout()->getQuote()->getId());
            /**
             * Current workaround depends on Onepage checkout model defect
             * Method Onepage::getCheckoutMethod performs setCheckoutMethod
             */
            $this->onepageCheckout->getCheckoutMethod();

            if ($controller == IframeConfigProvider::CHECKOUT_IDENTIFIER) {
                return $this->placeCheckoutOrder();
            }

            $params = $this->dataFactory
                ->create(DataFactory::AREA_FRONTEND)
                ->getSaveOrderUrlParams($controller);
            $this->_forward(
                $params['action'],
                $params['controller'],
                $params['module'],
                $this->getRequest()->getParams()
            );
        } else {
            $result = ['error_messages' => __('Please choose a payment method.'), 'goto_section' => 'payment'];
            if ($response instanceof Http) {
                $response->representJson($this->jsonHelper->jsonEncode($result));
            }
        }
    }

    /**
     * Place order for checkout flow
     *
     * @return void
     */
    protected function placeCheckoutOrder()
    {
        $result = new DataObject();
        $response = $this->getResponse();
        try {
            $this->cartManagement->placeOrder($this->_getCheckout()->getQuote()->getId());
            $result->setData('success', true);
            $this->eventManager->dispatch(
                'checkout_directpost_placeOrder',
                [
                    'result' => $result,
                    'action' => $this
                ]
            );
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception);
            $result->setData('error', true);
            $result->setData('error_messages', $exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $result->setData('error', true);
            $result->setData(
                'error_messages',
                __('A server error stopped your order from being placed. Please try to place your order again.')
            );
        }
        if ($response instanceof Http) {
            $response->representJson($this->jsonHelper->jsonEncode($result));
        }
    }
}
