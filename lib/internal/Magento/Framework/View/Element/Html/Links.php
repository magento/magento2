<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html;

/**
 * Links list block
 *
 * @api
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
     * Find link by path
     *
     * @param string $path
     * @return \Magento\Framework\View\Element\Html\Link
     */
    protected function getLinkByPath($path)
    {
        foreach ($this->getLinks() as $link) {
            if ($link->getPath() == $path) {
                return $link;
            }
        }
    }

    /**
     * Set active link
     *
     * @param string $path
     * @return void
     */
    public function setActive($path)
    {
        $link = $this->getLinkByPath($path);
        if ($link) {
            $link->setIsHighlighted(true);
        }
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
