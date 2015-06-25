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
 * Class VariableNotation
 * @package Magento\Framework\View\Asset\PreProcessor
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
