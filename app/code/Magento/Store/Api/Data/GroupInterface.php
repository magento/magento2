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
 * Group interface
 *
 * @api
 */
interface GroupInterface
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
