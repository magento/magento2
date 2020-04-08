<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\Framework\Config\DataInterface;

/**
 * Media gallery directory config
 */
class Config
{
    private const XML_PATH_BLACKLIST_PATTERNS = 'blacklist/patterns';

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

    /**
     * Returns list of blacklist regexp patterns
     *
     * @return array
     */
    public function getBlacklistPatterns() : array
    {
        return $this->get(self::XML_PATH_BLACKLIST_PATTERNS);
    }
}
