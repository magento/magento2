<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Inline;

class Provider implements ProviderInterface
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $inlineTranslate;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $inlineTranslate
     */
    public function __construct(\Magento\Framework\Translate\InlineInterface $inlineTranslate)
    {
        $this->inlineTranslate = $inlineTranslate;
    }

    /**
     * Return instance of inline translate class
     *
     * @return \Magento\Framework\Translate\InlineInterface
     */
    public function get()
    {
        return $this->inlineTranslate;
    }
}
