<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Framework\Controller\ResultFactory;

class GetClientToken extends Action
{
    const ADMIN_RESOURCE = 'Magento_Braintree::get_client_token';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BraintreeAdapterFactory
     */
    private $adapterFactory;

    /**
     * @var Quote
     */
    private $quoteSession;

    /**
     * @param Context $context
     * @param Config $config
     * @param BraintreeAdapterFactory $adapterFactory
     * @param Quote $quoteSession
     */
    public function __construct(
        Context $context,
        Config $config,
        BraintreeAdapterFactory $adapterFactory,
        Quote $quoteSession
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->adapterFactory = $adapterFactory;
        $this->quoteSession = $quoteSession;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $params = [];
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $storeId = $this->quoteSession->getStoreId();
        $merchantAccountId = $this->config->getMerchantAccountId($storeId);
        if (!empty($merchantAccountId)) {
            $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
        }

        $clientToken = $this->adapterFactory->create($storeId)
            ->generate($params);
        $response->setData(['clientToken' => $clientToken]);

        return $response;
    }
}
