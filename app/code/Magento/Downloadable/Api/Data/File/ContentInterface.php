<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Api\Data\File;

/**
 * @codeCoverageIgnore
 */
interface ContentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve data (base64 encoded content)
     *
     * @return string
     */
    public function getFileData();

    /**
     * Retrieve file name
     *
     * @return string
     */
    public function getName();
}
