<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\EavImageContentInterface;

/**
 * Image Data specific for Eav Model processing.
 */
class EavImageContent extends ImageContent implements EavImageContentInterface
{
    /**
     * Retrieve media data size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->_get(self::SIZE);
    }

    /**
     * Set media data size
     *
     * @param string $size
     * @return $this
     */
    public function setSize($size)
    {
        return $this->setData(self::SIZE, $size);
    }

    /**
     * Retrieve temporary directory location for saved images
     *
     * @return string
     */
    public function getTmpName()
    {
        return $this->_get(self::TMP_NAME);
    }

    /**
     * Set temporary directory location for saved images
     *
     * @param string $tmpName
     * @return $this
     */
    public function setTmpName($tmpName)
    {
        return $this->setData(self::TMP_NAME, $tmpName);
    }
}
