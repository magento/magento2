<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Framework\App\ObjectManager;
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
    public function __construct(
        CustomerSession $customerSession = null,
        StoreManager $storeManager = null
    ) {
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManager::class);
        $this->customerSession = $customerSession ?: ObjectManager::getInstance()
            ->get(CustomerSession::class);
    }

    /**
     * @inheritdoc
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
