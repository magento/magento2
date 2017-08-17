<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;

/**
 * Class \Magento\SalesRule\Model\Plugin\QuoteConfigProductAttributes
 *
 */
class QuoteConfigProductAttributes
{
    /**
     * @var RuleResource
     */
    protected $_ruleResource;

    /**
     * @param RuleResource $ruleResource
     */
    public function __construct(RuleResource $ruleResource)
    {
        $this->_ruleResource = $ruleResource;
    }

    /**
     * Append sales rule product attribute keys to select by quote item collection
     *
     * @param \Magento\Quote\Model\Quote\Config $subject
     * @param array $attributeKeys
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductAttributes(\Magento\Quote\Model\Quote\Config $subject, array $attributeKeys)
    {
        $attributes = $this->_ruleResource->getActiveAttributes();
        foreach ($attributes as $attribute) {
            $attributeKeys[] = $attribute['attribute_code'];
        }
        return $attributeKeys;
    }
}
