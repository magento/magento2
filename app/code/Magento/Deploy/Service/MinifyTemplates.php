<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Service;

use Magento\Framework\View\Template\Html\MinifierInterface;
use Magento\Framework\App\Utility\Files;

/**
 * Minify PHTML templates service
 * @since 2.2.0
 */
class MinifyTemplates
{
    /**
     * @var Files
     * @since 2.2.0
     */
    private $filesUtils;

    /**
     * @var MinifierInterface
     * @since 2.2.0
     */
    private $htmlMinifier;

    /**
     * @param Files $filesUtils
     * @param MinifierInterface $htmlMinifier
     * @since 2.2.0
     */
    public function __construct(
        Files $filesUtils,
        MinifierInterface $htmlMinifier
    ) {
        $this->filesUtils = $filesUtils;
        $this->htmlMinifier = $htmlMinifier;
    }

    /**
     * Minify template files
     *
     * @return int
     * @since 2.2.0
     */
    public function minifyTemplates()
    {
        $count = 0;
        foreach ($this->filesUtils->getPhtmlFiles(false, false) as $template) {
            $this->htmlMinifier->minify($template);
            $count++;
        }
        return $count;
    }
}
