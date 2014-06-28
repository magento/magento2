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

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use \Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Group price model
 */
class GroupPrice extends AbstractPrice implements GroupPriceInterface, BasePriceProviderInterface
{
    /**
     * Price type group
     */
    const PRICE_CODE = 'group_price';

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var array|null
     */
    protected $storedGroupPrice;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param Session $customerSession
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        Session $customerSession
    ) {
        parent::__construct($saleableItem, $quantity, $calculator);
        $this->customerSession = $customerSession;
    }

    /**
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value === null) {
            $this->value = false;
            $customerGroup = $this->getCustomerGroupId();
            foreach ($this->getStoredGroupPrice() as $groupPrice) {
                if ($groupPrice['cust_group'] == $customerGroup) {
                    $this->value = (float) $groupPrice['website_price'];
                    break;
                }
            }
        }
        return $this->value;
    }

    /**
     * @return int
     */
    protected function getCustomerGroupId()
    {
        if ($this->product->getCustomerGroupId()) {
            return (int) $this->product->getCustomerGroupId();
        }
        return (int) $this->customerSession->getCustomerGroupId();
    }

    /**
     * @return array
     */
    public function getStoredGroupPrice()
    {
        if (null === $this->storedGroupPrice) {
            $resource = $this->product->getResource();
            $attribute =  $resource->getAttribute('group_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($this->product);
                $this->storedGroupPrice = $this->product->getData('group_price');
            }
            if (null === $this->storedGroupPrice || !is_array($this->storedGroupPrice)) {
                $this->storedGroupPrice = [];
            }
        }
        return $this->storedGroupPrice;
    }
}
