<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Data;

/**
 * Image Data specific for Eav Model processing.
 */
interface EavImageContentInterface extends ImageContentInterface
{
    const SIZE = 'size';
    const TMP_NAME = 'tmp_name';

    /**
     * Retrieve media data size
     *
     * @return string
     */
    public function getSize();

    /**
     * Set media data size
     *
     * @param string $data
     * @return $this
     */
    public function setSize($data);

    /**
     * Retrieve temporary directory location for saved images
     *
     * @return string
     */
    public function getTmpName();

    /**
     * Set temporary directory location for saved images
     *
     * @param string $tmpName
     * @return $this
     */
    public function setTmpName($tmpName);
}
