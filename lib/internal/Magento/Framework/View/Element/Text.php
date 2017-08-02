<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * Class Text
 *
 * @api
 * @since 2.0.0
 */
class Text extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Set text data
     *
     * @param string $text
     * @return $this
     * @since 2.0.0
     */
    public function setText($text)
    {
        $this->setData('text', $text);
        return $this;
    }

    /**
     * Retrieve text data
     *
     * @return string
     * @since 2.0.0
     */
    public function getText()
    {
        return $this->getData('text');
    }

    /**
     * Append text before|after existing text data
     *
     * @param string $text
     * @param bool $before
     * @return void
     * @since 2.0.0
     */
    public function addText($text, $before = false)
    {
        if ($before) {
            $this->setText($text . $this->getText());
        } else {
            $this->setText($this->getText() . $text);
        }
    }

    /**
     * Render html output
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }
        return $this->getText();
    }
}
