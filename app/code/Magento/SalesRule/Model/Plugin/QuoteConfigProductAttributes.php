<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Plugin;

use Magento\Quote\Model\Quote\Config;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;

class QuoteConfigProductAttributes
{
    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * @var array|null
     */
    private $activeAttributeCodes;

    /**
     * @param RuleResource $ruleResource
     */
    public function __construct(RuleResource $ruleResource)
    {
        $this->ruleResource = $ruleResource;
    }

    /**
     * Append sales rule product attribute keys to select by quote item collection
     *
     * @param Config $subject
     * @param array $attributeKeys
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductAttributes(Config $subject, array $attributeKeys): array
    {
        if ($this->activeAttributeCodes === null) {
            $this->activeAttributeCodes = array_column($this->ruleResource->getActiveAttributes(), 'attribute_code');
        }

        return array_merge($attributeKeys, $this->activeAttributeCodes);
    }
}
