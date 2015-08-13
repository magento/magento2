<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api\Data;

/**
 * Group interface
 *
 * @api
 */
interface GroupInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @return int
     */
    public function getRootCategoryId();

    /**
     * @return int
     */
    public function getDefaultStoreId();

    /**
     * @return string
     */
    public function getName();
}
