<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
