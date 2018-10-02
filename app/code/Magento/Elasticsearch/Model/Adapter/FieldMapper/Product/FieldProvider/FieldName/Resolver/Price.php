<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param CustomerSession $customerSession
     * @param StoreManager $storeManager
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerSession $customerSession = null,
        StoreManager $storeManager = null
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Get field name for price type attributes.
     *
     * @param AttributeAdapter $attribute
     * @param array $context
     * @return string
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if ($attribute->getAttributeCode() === 'price') {
            $customerGroupId = !empty($context['customerGroupId'])
                ? $context['customerGroupId']
                : $this->customerSession->getCustomerGroupId();
            $websiteId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            try {
                $websiteId = !empty($context['websiteId'])
                    ? $context['websiteId']
                    : $this->storeManager->getStore()->getWebsiteId();
            } catch (LocalizedException $exception) {
                $this->logger->critical($exception);
            }

            return 'price_' . $customerGroupId . '_' . $websiteId;
        }

        return null;
    }
}
