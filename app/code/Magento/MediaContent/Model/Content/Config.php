<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model\Content;

use Magento\Framework\Config\DataInterface;

/**
 * Media content configuration
 */
class Config
{
    private const XML_PATH_SEARCH_PATTERNS = 'search/patterns';

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
     * Retrieve search regexp patterns for finding media asset paths within content
     *
     * @return array
     */
    public function getSearchPatterns(): array
    {
        return $this->get(self::XML_PATH_SEARCH_PATTERNS);
    }
}