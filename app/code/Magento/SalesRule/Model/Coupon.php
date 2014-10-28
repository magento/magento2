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
namespace Magento\SalesRule\Model;

/**
 * SalesRule Coupon Model
 *
 * @method \Magento\SalesRule\Model\Resource\Coupon _getResource()
 * @method \Magento\SalesRule\Model\Resource\Coupon getResource()
 * @method int getRuleId()
 * @method \Magento\SalesRule\Model\Coupon setRuleId(int $value)
 * @method string getCode()
 * @method \Magento\SalesRule\Model\Coupon setCode(string $value)
 * @method int getUsageLimit()
 * @method \Magento\SalesRule\Model\Coupon setUsageLimit(int $value)
 * @method int getUsagePerCustomer()
 * @method \Magento\SalesRule\Model\Coupon setUsagePerCustomer(int $value)
 * @method int getTimesUsed()
 * @method \Magento\SalesRule\Model\Coupon setTimesUsed(int $value)
 * @method string getExpirationDate()
 * @method \Magento\SalesRule\Model\Coupon setExpirationDate(string $value)
 * @method int getIsPrimary()
 * @method \Magento\SalesRule\Model\Coupon setIsPrimary(int $value)
 * @method int getType()
 * @method \Magento\SalesRule\Model\Coupon setType(int $value)
 */
class Coupon extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\SalesRule\Model\Resource\Coupon');
    }

    /**
     * Set rule instance
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return $this
     */
    public function setRule(\Magento\SalesRule\Model\Rule $rule)
    {
        $this->setRuleId($rule->getId());
        return $this;
    }

    /**
     * Load primary coupon for specified rule
     *
     * @param \Magento\SalesRule\Model\Rule|int $rule
     * @return $this
     */
    public function loadPrimaryByRule($rule)
    {
        $this->getResource()->loadPrimaryByRule($this, $rule);
        return $this;
    }

    /**
     * Load Shopping Cart Price Rule by coupon code
     *
     * @param string $couponCode
     * @return $this
     */
    public function loadByCode($couponCode)
    {
        $this->load($couponCode, 'code');
        return $this;
    }
}
