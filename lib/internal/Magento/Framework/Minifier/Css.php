<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Minifier;

use CSSmin;

class Css implements CssInterface
{
    /**
     * @var CSSmin
     */
    protected $cssMinifier;

    /**
     * @param CSSmin $cssMinifier
     */
    public function __construct(CSSmin $cssMinifier)
    {
        $this->cssMinifier = $cssMinifier;
    }

    /**
     * Minify content of chain object with css minifier. Files named with suffix '.min.' are not processed
     *
     * @param \Magento\Framework\View\Asset\PreProcessor\Chain $chain
     * @return void
     */
    public function process(\Magento\Framework\View\Asset\PreProcessor\Chain $chain)
    {
        if (!preg_match('/\.min\.[^\.]+$/', $chain->getAsset()->getFilePath())) {
            $chain->setContent($this->cssMinifier->run($chain->getContent()));
        }
    }
}
