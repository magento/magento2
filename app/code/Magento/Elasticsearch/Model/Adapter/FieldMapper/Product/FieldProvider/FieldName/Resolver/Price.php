<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Resolver field name for price attribute.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
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
        CustomerSession $customerSession,
        StoreManager $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
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
