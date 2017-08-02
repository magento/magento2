<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Accordion;

use Magento\Backend\Block\Widget\Accordion;

/**
 * Accordion item
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Item extends \Magento\Backend\Block\Widget
{
    /**
     * @var Accordion
     * @since 2.0.0
     */
    protected $_accordion;

    /**
     * @param Accordion $accordion
     * @return $this
     * @since 2.0.0
     */
    public function setAccordion($accordion)
    {
        $this->_accordion = $accordion;
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTarget()
    {
        return $this->getAjax() ? 'ajax' : '';
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTitle()
    {
        $title = $this->getData('title');
        $url = $this->getContentUrl() ? $this->getContentUrl() : '#';
        $title = '<a href="' . $url . '" class="' . $this->getTarget() . '"' . $this->getUiId(
            'title-link'
        ) . '>' . $title . '</a>';

        return $title;
    }

    /**
     * @return null|string
     * @since 2.0.0
     */
    public function getContent()
    {
        $content = $this->getData('content');
        if (is_string($content)) {
            return $content;
        }
        if ($content instanceof \Magento\Framework\View\Element\AbstractBlock) {
            return $content->toHtml();
        }
        return null;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getClass()
    {
        $class = $this->getData('class');
        if ($this->getOpen()) {
            $class .= ' open';
        }
        return $class;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $content = $this->getContent();
        $html = '<dt id="dt-' . $this->getHtmlId() . '" class="' . $this->getClass() . '"';
        $html .= $this->getUiId() . '>';
        $html .= $this->getTitle();
        $html .= '</dt>';
        $html .= '<dd id="dd-' . $this->getHtmlId() . '" class="' . $this->getClass() . '">';
        $html .= $content;
        $html .= '</dd>';
        return $html;
    }
}
