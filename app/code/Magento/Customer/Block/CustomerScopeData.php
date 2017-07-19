<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class CustomerScopeData provide scope (website, store or store_group) information on front
 * Can be used, for example, on store front, in order to determine
 * that private cache invalid for current scope, by comparing
 * with appropriate value in store front private cache.
 * @api
 */
class CustomerScopeData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $storeManager;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     * @param Json|null $serializer
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $context->getStoreManager();
        $this->serializer = $serializer?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Return id of current website
     *
     * Can be used when necessary to obtain website id of the current customer.
     *
     * @return integer
     */
    public function getWebsiteId()
    {
        return (int)$this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * Encode invalidation rules.
     *
     * @param array $configuration
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    public function encodeConfiguration(array $configuration)
    {
        return $this->serializer->serialize($configuration);
    }
}
