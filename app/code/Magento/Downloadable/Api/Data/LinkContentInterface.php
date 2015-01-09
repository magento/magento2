<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Api\Data;

/**
 * @codeCoverageIgnore
 */
interface LinkContentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve sample title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Retrieve sample sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Retrieve link price
     *
     * @return string
     */
    public function getPrice();

    /**
     * Retrieve number of allowed downloads of the link
     *
     * @return int
     */
    public function getNumberOfDownloads();

    /**
     * Check if link is shareable
     *
     * @return bool
     */
    public function isShareable();

    /**
     * Retrieve link file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getLinkFile();

    /**
     * Retrieve link URL
     *
     * @return string|null
     */
    public function getLinkUrl();

    /**
     * Retrieve link type ('url' or 'file')
     *
     * @return string|null
     */
    public function getLinkType();

    /**
     * Retrieve sample file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getSampleFile();

    /**
     * Retrieve sample URL
     *
     * @return string|null
     */
    public function getSampleUrl();

    /**
     * Retrieve sample type ('url' or 'file')
     *
     * @return string|null
     */
    public function getSampleType();
}
