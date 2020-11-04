<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefrontElasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\SearchStorefrontElasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\SearchStorefrontElasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Resolver field name for price attribute.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Price implements ResolverInterface
{
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param StoreManager $storeManager
     */
    public function __construct(
        StoreManager $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if ($attribute->getAttributeCode() === 'price') {
            //temp solution for passing customerGroupId with stub store model
            $customerGroupId = !empty($context['customerGroupId'])
                ? $context['customerGroupId']
                : $this->storeManager->getStore($context['storeId']??null)->getCustomerGroupId();
            $websiteId = !empty($context['websiteId'])
                ? $context['websiteId']
                : $this->storeManager->getStore($context['storeId']??null)->getWebsiteId();

            return 'price_' . $customerGroupId . '_' . $websiteId;
        }

        return null;
    }
}
