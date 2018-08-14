<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File\Export;

/**
 * Data mapping for Export file.
 */
class Data
{
    /**
     * File data.
     *
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get file name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->data['name'];
    }

    /**
     * Get file content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->data['content'];
    }

    /**
     * Get file creation date.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->data['date'];
    }
}
