<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\SalesRule\Model\Resource\Rule as ResourceRule;

class QuoteConfigProductAttributes
{
    /**
     * @var ResourceRule
     */
    protected $_ruleResource;

    /**
     * @param ResourceRule $ruleResource
     */
    public function __construct(ResourceRule $ruleResource)
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
