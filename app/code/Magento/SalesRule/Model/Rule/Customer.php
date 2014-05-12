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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model\Rule;

/**
 * SalesRule Rule Customer Model
 *
 * @method \Magento\SalesRule\Model\Resource\Rule\Customer _getResource()
 * @method \Magento\SalesRule\Model\Resource\Rule\Customer getResource()
 * @method int getRuleId()
 * @method \Magento\SalesRule\Model\Rule\Customer setRuleId(int $value)
 * @method int getCustomerId()
 * @method \Magento\SalesRule\Model\Rule\Customer setCustomerId(int $value)
 * @method int getTimesUsed()
 * @method \Magento\SalesRule\Model\Rule\Customer setTimesUsed(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\SalesRule\Model\Resource\Rule\Customer');
    }

    /**
     * Load by customer rule
     *
     * @param int $customerId
     * @param int $ruleId
     * @return $this
     */
    public function loadByCustomerRule($customerId, $ruleId)
    {
        $this->_getResource()->loadByCustomerRule($this, $customerId, $ruleId);
        return $this;
    }
}
