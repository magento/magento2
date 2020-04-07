<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaGallery\Model\Directory;

use Magento\Framework\Config\DataInterface;

/**
 * Config of Magento Media Gallery Directory.
 */
class Config
{
    /**
     * @var DataInterface
     */
    private $data;

    /**
     * @param DataInterface $data
     */
    public function __construct(DataInterface $data)
    {
        $this->data = $data;
    }

    /**
     * Get config value by key.
     *
     * @param string|null $key
     * @param string|null $default
     * @return array
     */
    public function get($key = null, $default = null)
    {
        return $this->data->get($key, $default);
    }
}
