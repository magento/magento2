<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * MassAction interface.
 * @api
 * @since 101.1.0
 */
interface MassActionInterface
{

    /**
     * Set data value.
     *
     * @param string[] $data
     * @return void
     * @since 101.1.0
     */
    public function setInventory($data);

    /**
     * Get data value.
     *
     * @return string[]
     * @since 101.1.0
     */
    public function getInventory():array;

    /**
     * Set data value.
     *
     * @param string[] $data
     * @return void
     * @since 101.1.0
     */
    public function setAttributes($data);

    /**
     * Get data value.
     *
     * @return string[]
     * @since 101.1.0
     */
    public function getAttributes():array;

    /**
     * Set data value.
     *
     * @param string[] $data
     * @return void
     * @since 101.1.0
     */
    public function setWebsiteRemove($data);

    /**
     * Get data value.
     *
     * @return string[]
     * @since 101.1.0
     */
    public function getWebsiteRemove():array;

    /**
     * Set data value.
     *
     * @param string[] $data
     * @return void
     * @since 101.1.0
     */
    public function setWebsiteAdd($data);

    /**
     * Get data value.
     *
     * @return string[]
     * @since 101.1.0
     */
    public function getWebsiteAdd():array;

    /**
     * Set data value.
     *
     * @param integer $data
     * @return void
     * @since 101.1.0
     */
    public function setStoreId($data);

    /**
     * Get data value.
     *
     * @return integer
     * @since 101.1.0
     */
    public function getStoreId();

    /**
     * Set data value.
     *
     * @param integer[] $data
     * @return void
     * @since 101.1.0
     */
    public function setProductIds(array $data);

    /**
     * Get data value.
     *
     * @return integer[]
     * @since 101.1.0
     */
    public function getProductIds():array;

    /**
     * Set data value.
     *
     * @param integer $data
     * @return void
     * @since 101.1.0
     */
    public function setWebsiteId($data);

    /**
     * Get data value.
     *
     * @return integer
     * @since 101.1.0
     */
    public function getWebsiteId();
}
