<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api\Data;

/**
 * Available Currency interface.
 *
 * @api
 * @since 100.0.2
 */
interface AvailableCurrencyInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get the currency code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set the currency code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get the currency value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set the currency value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get the currency name
     *
     * @return string
     */
    public function getName();

    /**
     * Set the currency name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get the currency symbol
     *
     * @return string
     */
    public function getSymbol();

    /**
     * Set the currency symbol
     *
     * @param  $symbol
     * @return $this
     */
    public function setSymbol($symbol);
}
