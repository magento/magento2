<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface TotalsInterface
 * @api
 * @since 2.0.0
 */
interface TotalSegmentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const CODE  = 'code';
    const TITLE = 'title';
    const VALUE = 'value';
    const AREA  = 'area';
    /**#@-*/

    /**
     * Total code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set total code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get total title
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Set total title
     *
     * @param string|null $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title = null);

    /**
     * Get total value
     *
     * @return float
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set total value
     *
     * @param float $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value);

    /**
     * Get display area code.
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getArea();

    /**
     * Set display area code
     *
     * @param string|null $area
     * @return $this
     * @since 2.0.0
     */
    public function setArea($area = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\TotalSegmentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\TotalSegmentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\TotalSegmentExtensionInterface $extensionAttributes
    );
}
