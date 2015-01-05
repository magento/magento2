<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Api\Data;


/**
 * @codeCoverageIgnore
 */
interface SampleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * Product sample id
     *
     * @return int|null Sample(or link) id
     */
    public function getId();

    /**
     * Sample title
     *
     * @return string
     */
    public function getTitle();

    /**
     * File or URL of sample
     *
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo
     */
    public function getSampleResource();

    /**
     * Sort order index for sample
     *
     * @return int
     */
    public function getSortOrder();

}
