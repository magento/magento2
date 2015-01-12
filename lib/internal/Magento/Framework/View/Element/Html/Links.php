<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html;

/**
 * Links list block
 */
class Links extends \Magento\Framework\View\Element\Template
{
    /**
     * Get links
     *
     * @return \Magento\Framework\View\Element\Html\Link[]
     */
    public function getLinks()
    {
        return $this->_layout->getChildBlocks($this->getNameInLayout());
    }

    /**
     * Render Block
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $link
     * @return string
     */
    public function renderLink(\Magento\Framework\View\Element\AbstractBlock $link)
    {
        return $this->_layout->renderElement($link->getNameInLayout());
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $html = '';
        if ($this->getLinks()) {
            $html = '<ul' . ($this->hasCssClass() ? ' class="' . $this->escapeHtml(
                $this->getCssClass()
            ) . '"' : '') . '>';
            foreach ($this->getLinks() as $link) {
                $html .= $this->renderLink($link);
            }
            $html .= '</ul>';
        }

        return $html;
    }
}
