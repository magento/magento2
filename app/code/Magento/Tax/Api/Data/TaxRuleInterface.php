<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Tax rule interface.
 * @api
 */
interface TaxRuleInterface extends ExtensibleDataInterface
{
    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get tax rule code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set tax rule code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get priority
     *
     * @return int
     */
    public function getPriority();

    /**
     * Set priority
     *
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * Get sort order.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set sort order.
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get customer tax class id
     *
     * @return int[]
     */
    public function getCustomerTaxClassIds();

    /**
     * Set customer tax class id
     *
     * @param int[] $customerTaxClassIds
     * @return $this
     */
    public function setCustomerTaxClassIds(array $customerTaxClassIds = null);

    /**
     * Get product tax class id
     *
     * @return int[]
     */
    public function getProductTaxClassIds();

    /**
     * Set product tax class id
     *
     * @param int[] $productTaxClassIds
     * @return $this
     */
    public function setProductTaxClassIds(array $productTaxClassIds = null);

    /**
     * Get tax rate ids
     *
     * @return int[]
     */
    public function getTaxRateIds();

    /**
     * Set tax rate ids
     *
     * @param int[] $taxRateIds
     * @return $this
     */
    public function setTaxRateIds(array $taxRateIds = null);

    /**
     * Get calculate subtotal.
     *
     * @return bool|null
     */
    public function getCalculateSubtotal();

    /**
     * Set calculate subtotal.
     *
     * @param bool $calculateSubtotal
     * @return $this
     */
    public function setCalculateSubtotal($calculateSubtotal);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxRuleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxRuleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRuleExtensionInterface $extensionAttributes);
}
