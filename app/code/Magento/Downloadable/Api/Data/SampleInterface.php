<?php
/**
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
     * Sort order index for sample
     *
     * @return int
     */
    public function getSortOrder();

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
