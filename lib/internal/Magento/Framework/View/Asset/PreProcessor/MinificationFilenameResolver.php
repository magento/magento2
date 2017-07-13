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
     * Constructor
     *
     * @param Minification $minification
     */
    public function __construct(Minification $minification)
    {
        $this->minification = $minification;
    }

    /**
     * Resolve file name
     *
     * @param string $path
     * @return string
     */
    public function resolve($path)
    {
        if (!$this->minification->isEnabled(pathinfo($path, PATHINFO_EXTENSION))) {
            return $path;
        }

        return str_replace(self::FILE_PART, '.', $path);
    }
}
