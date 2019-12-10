<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\Minification;

/**
 * Class MinificationFilenameResolver
 */
class MinificationFilenameResolver implements FilenameResolverInterface
{
    /**
     * Indicator of minification file
     */
    const FILE_PART = '.min.';

    /**
     * @var Minification
     */
    private $minification;

    /**
     * @var MinificationConfigProvider
     */
    private $minificationConfig;

    /**
     * Constructor
     *
     * @param Minification $minification
     * @param MinificationConfigProvider $minificationConfig
     */
    public function __construct(
        Minification $minification,
        MinificationConfigProvider $minificationConfig
    ) {
        $this->minification = $minification;
        $this->minificationConfig = $minificationConfig;
    }

    /**
     * Resolve file name
     *
     * @param string $path
     * @return string
     */
    public function resolve($path)
    {
        $result = $path;
        if ($this->minificationConfig->isMinificationEnabled($path)) {
            $result = str_replace(self::FILE_PART, '.', $path);
        }

        return $result;
    }
}
