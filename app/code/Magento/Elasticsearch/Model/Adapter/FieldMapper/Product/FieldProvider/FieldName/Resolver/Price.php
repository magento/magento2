<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Resolver field name for price attribute.
 */
class Price implements ResolverInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param CustomerSession $customerSession
     * @param StoreManager $storeManager
     */
    public function __construct(CustomerSession $customerSession, StoreManager $storeManager)
    {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if ($attribute->getAttributeCode() === 'price') {
            $customerGroupId = !empty($context['customerGroupId'])
                ? $context['customerGroupId']
                : $this->customerSession->getCustomerGroupId();
            $websiteId = !empty($context['websiteId'])
                ? $context['websiteId']
                : $this->storeManager->getStore()->getWebsiteId();

            return 'price_' . $customerGroupId . '_' . $websiteId;
        }

        return null;
    }
}
