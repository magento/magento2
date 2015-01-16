<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
