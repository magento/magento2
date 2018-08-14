<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

/**
 * Class CustomerScopeData provide scope (website, store or store_group) information on front
 * Can be used, for example, on store front, in order to determine
 * that private cache invalid for current scope, by comparing
 * with appropriate value in store front private cache.
 * @api
 * @since 100.2.0
 */
class CustomerScopeData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     * @since 100.2.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $context->getStoreManager();
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Return id of current website
     *
     * Can be used when necessary to obtain website id of the current customer.
     *
     * @return integer
     * @since 100.2.0
     */
    public function getWebsiteId()
    {
        return (int)$this->_storeManager->getStore()->getWebsiteId();
    }
}
