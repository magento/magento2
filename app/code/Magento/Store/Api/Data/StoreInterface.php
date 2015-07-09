<?php
/**
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Store\Api\Data;

/**
 * Store interface
 *
 * @api
 */
interface StoreInterface
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
    public function getWebsiteId();
}
