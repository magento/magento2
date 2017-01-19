<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface TotalsInterface
 * @api
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
     */
    public function getCode();

    /**
     * Set total code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get total title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set total title
     *
     * @param string|null $title
     * @return $this
     */
    public function setTitle($title = null);

    /**
     * Get total value
     *
     * @return float
     */
    public function getValue();

    /**
     * Set total value
     *
     * @param float $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get display area code.
     *
     * @return string|null
     */
    public function getArea();

    /**
     * Set display area code
     *
     * @param string|null $area
     * @return $this
     */
    public function setArea($area = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\TotalSegmentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\TotalSegmentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\TotalSegmentExtensionInterface $extensionAttributes
    );
}
