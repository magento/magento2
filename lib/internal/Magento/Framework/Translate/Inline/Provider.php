<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Inline;

/**
 * Class \Magento\Framework\Translate\Inline\Provider
 *
 * @since 2.0.0
 */
class Provider implements ProviderInterface
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     * @since 2.0.0
     */
    protected $inlineTranslate;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $inlineTranslate
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Translate\InlineInterface $inlineTranslate)
    {
        $this->inlineTranslate = $inlineTranslate;
    }

    /**
     * Return instance of inline translate class
     *
     * @return \Magento\Framework\Translate\InlineInterface
     * @since 2.0.0
     */
    public function get()
    {
        return $this->inlineTranslate;
    }
}
