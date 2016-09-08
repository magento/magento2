<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Deploy;

use Magento\Framework\View\Template\Html\MinifierInterface;
use Magento\Framework\View\Asset\ConfigInterface as AssetConfig;
use Magento\Framework\App\Utility\Files;

class TemplateMinifier
{
    /**
     * @var AssetConfig
     */
    private $assetConfig;

    /**
     * @var Files
     */
    private $filesUtils;

    /**
     * @var MinifierInterface
     */
    private $htmlMinifier;

    /**
     * @param AssetConfig $assetConfig
     * @param Files $filesUtils
     * @param MinifierInterface $htmlMinifier
     */
    public function __construct(
        AssetConfig $assetConfig,
        Files $filesUtils,
        MinifierInterface $htmlMinifier
    ) {
        $this->assetConfig = $assetConfig;
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
        if ($this->assetConfig->isMinifyHtml()) {
            foreach ($this->filesUtils->getPhtmlFiles(false, false) as $template) {
                $this->htmlMinifier->minify($template);
                $minified++;
            }
        }

        return $minified;
    }
}
