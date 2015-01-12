<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\DesignEditor\Model\Translate\Inline;

class Provider extends \Magento\Framework\Translate\Inline\Provider
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $vdeInlineTranslate;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $inlineTranslate;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $vdeInlineTranslate
     * @param \Magento\Framework\Translate\InlineInterface $inlineTranslate
     * @param \Magento\DesignEditor\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Translate\InlineInterface $vdeInlineTranslate,
        \Magento\Framework\Translate\InlineInterface $inlineTranslate,
        \Magento\DesignEditor\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->vdeInlineTranslate = $vdeInlineTranslate;
        $this->inlineTranslate = $inlineTranslate;
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * Return instance of inline translate class
     *
     * @return \Magento\Framework\Translate\InlineInterface
     */
    public function get()
    {
        return $this->helper->isVdeRequest($this->request)
            ? $this->vdeInlineTranslate
            : $this->inlineTranslate;
    }
}
