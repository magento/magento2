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
 */
class MinifyTemplates
{
    /**
     * @var Files
     */
    private $filesUtils;

    /**
     * @var MinifierInterface
     */
    private $htmlMinifier;

    /**
     * @param Files $filesUtils
     * @param MinifierInterface $htmlMinifier
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
