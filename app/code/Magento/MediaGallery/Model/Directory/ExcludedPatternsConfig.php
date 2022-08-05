<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\Framework\Config\DataInterface;
use Magento\MediaGalleryApi\Model\ExcludedPatternsConfigInterface;

/**
 * Media gallery directory config
 * @deprecated
 */
class ExcludedPatternsConfig implements ExcludedPatternsConfigInterface
{
    private const XML_PATH_EXCLUDED_PATTERNS = 'exclude/patterns';

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
     * Returns list of blacklist regexp patterns
     *
     * @return array
     */
    public function get() : array
    {
        return $this->data->get(self::XML_PATH_EXCLUDED_PATTERNS);
    }
}
