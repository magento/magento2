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
 */
class CustomerScopeData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $context->getStoreManager();
        $this->serializer = $serializer;
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
     * Get the invalidation rules json encoded
     *
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    public function getSerializedInvalidationRules()
    {
        return $this->serializer->serialize(
            [
                '*' => [
                    'Magento_Customer/js/invalidation-processor' => [
                        'invalidationRules' => [
                            'website-rule' => [
                                'Magento_Customer/js/invalidation-rules/website-rule' => [
                                    'scopeConfig' => [
                                        'websiteId' => $this->getWebsiteId(),
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}
