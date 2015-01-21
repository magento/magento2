<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api\Data;

interface OptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Get option title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get is required option
     *
     * @return bool|null
     */
    public function getRequired();

    /**
     * Get input type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Get option position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Get product sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Get product links
     *
     * @return \Magento\Bundle\Api\Data\LinkInterface[]|null
     */
    public function getProductLinks();
}
