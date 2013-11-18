<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model\Plugin;

class QuoteConfigProductAttributes
{
    /** @var \Magento\SalesRule\Model\Resource\Rule */
    protected $_ruleResource;

    /** @var \Magento\Customer\Model\Session */
    protected $_customerSession;

    /** @var \Magento\Core\Model\StoreManager */
    protected $_storeManager;

    /**
     * @param \Magento\SalesRule\Model\Resource\Rule $ruleResource
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Model\StoreManager $storeManager
     */
    public function __construct(
        \Magento\SalesRule\Model\Resource\Rule $ruleResource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Model\StoreManager $storeManager
    ) {
        $this->_ruleResource = $ruleResource;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
    }

    /**
     * Append sales rule product attribute keys to select by quote item collection
     *
     * @param array $attributeKeys
     * @return array
     */
    public function afterGetProductAttributes(array $attributeKeys)
    {
        $attributes = $this->_ruleResource->getActiveAttributes(
            $this->_storeManager->getWebsite()->getId(),
            $this->_customerSession->getCustomer()->getGroupId()
        );

        foreach ($attributes as $attribute) {
            $attributeKeys[] = $attribute['attribute_code'];
        }

        return $attributeKeys;
    }
}
