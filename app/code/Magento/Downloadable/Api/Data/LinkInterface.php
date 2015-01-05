<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * File or URL of sample if any
     *
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo|null
     */
    public function getSampleResource();


    /**
     * File or URL of link
     *
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo
     */
    public function getLinkResource();
}
