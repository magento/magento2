<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api\Data;

/**
 * Website interface
 *
 * @api
 */
interface WebsiteInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return int
     */
    public function getDefaultGroupId();
}
