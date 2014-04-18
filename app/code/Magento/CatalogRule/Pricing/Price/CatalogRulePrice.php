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
 * @category    Magento
 * @package     Magento_CatalogRule
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogRule\Pricing\Price;

use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Pricing\Adjustment\Calculator;
use Magento\Pricing\Object\SaleableInterface;
use Magento\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManager;
use Magento\Customer\Model\Session;
use Magento\CatalogRule\Model\Resource\RuleFactory;

/**
 * Class CatalogRulePrice
 */
class CatalogRulePrice extends RegularPrice
{
    /**
     * Price type identifier string
     */
    const PRICE_TYPE = 'catalog_rule_price';

    /**
     * @var string
     */
    protected $priceType = self::PRICE_TYPE;

    /**
     * @var \Magento\Stdlib\DateTime\TimezoneInterface
     */
    protected $dateTime;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\CatalogRule\Model\Resource\RuleFactory
     */
    protected $resourceRuleFactory;

    /**
     * @param SaleableInterface $salableItem
     * @param float $quantity
     * @param Calculator $calculator
     * @param TimezoneInterface $dateTime
     * @param StoreManager $storeManager
     * @param Session $customerSession
     * @param RuleFactory $catalogRuleResourceFactory
     */
    public function __construct(
        SaleableInterface $salableItem,
        $quantity,
        Calculator $calculator,
        TimezoneInterface $dateTime,
        StoreManager $storeManager,
        Session $customerSession,
        RuleFactory $catalogRuleResourceFactory
    ) {
        parent::__construct($salableItem, $quantity, $calculator);
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->resourceRuleFactory = $catalogRuleResourceFactory;
    }

    /**
     * Returns catalog rule value
     *
     * @return float|boolean
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = $this->resourceRuleFactory->create()
                ->getRulePrice(
                    $this->dateTime->scopeTimeStamp($this->storeManager->getStore()->getId()),
                    $this->storeManager->getStore()->getWebsiteId(),
                    $this->customerSession->getCustomerGroupId(),
                    $this->salableItem->getId()
                );
            $this->value = $this->value ? floatval($this->value) : false;
        }
        return $this->value;
    }
}
