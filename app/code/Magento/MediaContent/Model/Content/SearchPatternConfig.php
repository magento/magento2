<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model\Content;

use Magento\Framework\Config\DataInterface;
use Magento\MediaContentApi\Model\SearchPatternConfigInterface;

/**
 * Media content configuration
 */
class SearchPatternConfig implements SearchPatternConfigInterface
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
     * Retrieve search RegExp patterns for finding media asset paths within content
     *
     * @return array
     */
    public function get(): array
    {
        return $this->data->get(self::XML_PATH_SEARCH_PATTERNS);
    }
}
