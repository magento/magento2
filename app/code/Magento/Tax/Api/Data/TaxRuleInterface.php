<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TaxRuleInterface extends ExtensibleDataInterface
{
    /**
     * Get id
     *
     * @api
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get tax rule code
     *
     * @api
     * @return string
     */
    public function getCode();

    /**
     * Set tax rule code
     *
     * @api
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get priority
     *
     * @api
     * @return int
     */
    public function getPriority();

    /**
     * Set priority
     *
     * @api
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * Get sort order.
     *
     * @api
     * @return int
     */
    public function getPosition();

    /**
     * Set sort order.
     *
     * @api
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get customer tax class id
     *
     * @api
     * @return int[]
     */
    public function getCustomerTaxClassIds();

    /**
     * Set customer tax class id
     *
     * @api
     * @param int[] $customerTaxClassIds
     * @return $this
     */
    public function setCustomerTaxClassIds(array $customerTaxClassIds = null);

    /**
     * Get product tax class id
     *
     * @api
     * @return int[]
     */
    public function getProductTaxClassIds();

    /**
     * Set product tax class id
     *
     * @api
     * @param int[] $productTaxClassIds
     * @return $this
     */
    public function setProductTaxClassIds(array $productTaxClassIds = null);

    /**
     * Get tax rate ids
     *
     * @api
     * @return int[]
     */
    public function getTaxRateIds();

    /**
     * Set tax rate ids
     *
     * @api
     * @param int[] $taxRateIds
     * @return $this
     */
    public function setTaxRateIds(array $taxRateIds = null);

    /**
     * Get calculate subtotal.
     *
     * @api
     * @return bool|null
     */
    public function getCalculateSubtotal();

    /**
     * Set calculate subtotal.
     *
     * @api
     * @param bool $calculateSubtotal
     * @return $this
     */
    public function setCalculateSubtotal($calculateSubtotal);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\TaxRuleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\TaxRuleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRuleExtensionInterface $extensionAttributes);
}
