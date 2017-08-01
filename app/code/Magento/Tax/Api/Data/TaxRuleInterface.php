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
 * @since 2.0.0
 */
interface TaxRuleInterface extends ExtensibleDataInterface
{
    /**
     * Get id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get tax rule code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set tax rule code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get priority
     *
     * @return int
     * @since 2.0.0
     */
    public function getPriority();

    /**
     * Set priority
     *
     * @param int $priority
     * @return $this
     * @since 2.0.0
     */
    public function setPriority($priority);

    /**
     * Get sort order.
     *
     * @return int
     * @since 2.0.0
     */
    public function getPosition();

    /**
     * Set sort order.
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position);

    /**
     * Get customer tax class id
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getCustomerTaxClassIds();

    /**
     * Set customer tax class id
     *
     * @param int[] $customerTaxClassIds
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerTaxClassIds(array $customerTaxClassIds = null);

    /**
     * Get product tax class id
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getProductTaxClassIds();

    /**
     * Set product tax class id
     *
     * @param int[] $productTaxClassIds
     * @return $this
     * @since 2.0.0
     */
    public function setProductTaxClassIds(array $productTaxClassIds = null);

    /**
     * Get tax rate ids
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getTaxRateIds();

    /**
     * Set tax rate ids
     *
     * @param int[] $taxRateIds
     * @return $this
     * @since 2.0.0
     */
    public function setTaxRateIds(array $taxRateIds = null);

    /**
     * Get calculate subtotal.
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getCalculateSubtotal();

    /**
     * Set calculate subtotal.
     *
     * @param bool $calculateSubtotal
     * @return $this
     * @since 2.0.0
     */
    public function setCalculateSubtotal($calculateSubtotal);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxRuleExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxRuleExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRuleExtensionInterface $extensionAttributes);
}
