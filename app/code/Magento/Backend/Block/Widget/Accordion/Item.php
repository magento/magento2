<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Accordion;

use Magento\Backend\Block\Widget\Accordion;

/**
 * Accordion item
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Backend\Block\Widget
{
    /**
     * @var Accordion
     */
    protected $_accordion;

    /**
     * @param Accordion $accordion
     * @return $this
     */
    public function setAccordion($accordion)
    {
        $this->_accordion = $accordion;
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->getAjax() ? 'ajax' : '';
    }

    /**
     * @return string
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
