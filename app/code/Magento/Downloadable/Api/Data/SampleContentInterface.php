<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Api\Data;

/**
 * @codeCoverageIgnore
 */
interface SampleContentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve sample title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Retrieve sample type ('url' or 'file')
     *
     * @return string|null
     */
    public function getSampleType();

    /**
     * Retrieve sample file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getSampleFile();

    /**
     * Retrieve sample sort order
     *
     * @return int
     */
    public function getSortOrder();


    /**
     * Retrieve sample URL
     *
     * @return string|null
     */
    public function getSampleUrl();

}
