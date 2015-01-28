<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * @codeCoverageIgnore
 */
interface LinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * @return int|null Sample(or link) id
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * Link shareable status
     * 0 -- No
     * 1 -- Yes
     * 2 -- Use config default value
     *
     * @return int
     */
    public function getIsShareable();

    /**
     * Link price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Number of downloads per user
     * Null for unlimited downloads
     *
     * @return int|null
     */
    public function getNumberOfDownloads();

    /**
     * @return string
     */
    public function getLinkType();

    /**
     * Return file path or null when type is 'url'
     *
     * @return string|null relative file path
     */
    public function getLinkFile();

    /**
     * Return URL or NULL when type is 'file'
     *
     * @return string|null file URL
     */
    public function getLinkUrl();

    /**
     * @return string
     */
    public function getSampleType();

    /**
     * Return file path or null when type is 'url'
     *
     * @return string|null relative file path
     */
    public function getSampleFile();

    /**
     * Return URL or NULL when type is 'file'
     *
     * @return string|null file URL
     */
    public function getSampleUrl();
}
