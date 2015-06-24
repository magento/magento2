<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset;
use Magento\Framework\View\Asset\NotationResolver;
use Magento\Framework\View\Url\CssResolver;

/**
 * Support of notation "{{var}}::file/path.ext" in CSS-files
 *
 * This exists such that a CSS file may refer to another file without knowing where the referenced file will
 * ultimately reside from when loaded, and no URL to . Context of
 * this url path can be known ONLY when the CSS is being procsessed since it is dependent on the design config.
 */
class VariableNotation implements Asset\PreProcessorInterface
{
    /**
     * @var \Magento\Framework\View\Url\CssResolver
     */
    private $cssResolver;

    /**
     * @var NotationResolver\Variable
     */
    private $notationResolver;

    /**
     * @param CssResolver $cssResolver
     * @param NotationResolver\Variable $notationResolver
     */
    public function __construct(
        CssResolver $cssResolver,
        NotationResolver\Variable $notationResolver
    ) {
        $this->cssResolver = $cssResolver;
        $this->notationResolver = $notationResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Chain $chain)
    {
        $callback = function ($path) {
            return $this->notationResolver->convertVariableNotation($path);
        };
        $chain->setContent($this->cssResolver->replaceRelativeUrls($chain->getContent(), $callback));
    }
}
