<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Resolver;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;

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
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName($attributeCode, $context = [])
    {
        $customerGroupId = !empty($context['customerGroupId'])
            ? $context['customerGroupId']
            : $this->customerSession->getCustomerGroupId();

        return 'price_' . $customerGroupId;
    }
}
