<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Deploy;

use Magento\Framework\View\Template\Html\MinifierInterface;
use Magento\Framework\App\Utility\Files;

class TemplateMinifier
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
     * @return int
     */
    public function minifyTemplates()
    {
        $minified = 0;
        foreach ($this->filesUtils->getPhtmlFiles(false, false) as $template) {
            $this->htmlMinifier->minify($template);
            $minified++;
        }
        return $minified;
    }
}
