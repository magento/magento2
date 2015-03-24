<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html\Topmenu;

use Magento\Theme\Block\Html\Topmenu;

class Renderer extends Topmenu
{
    protected $_template = 'html/topmenu/renderer.phtml';

    protected function _toHtml()
    {
        $menuTree = $this->getMenuTree();
        $childrenWrapClass = $this->getChildrenWrapClass();

        if(!$this->getTemplate() || is_null($menuTree) || is_null($childrenWrapClass)) {
            throw new \Magento\Framework\Exception("Top-menu renderer isn't fully configured.");
        }

        if (!$this->isTemplateFileValid($this->getTemplateFile())) {
            throw new \Magento\Framework\Exception('Not valid template file:' . $this->_templateFile);
        }

        return $this->render($menuTree,$childrenWrapClass);
    }

    public function render($menuTree,$childrenWrapClass,$limit=0,$colBreaks = [])
    {
        $this->assign('menuTree',$menuTree);
        $this->assign('childrenWrapClass',$childrenWrapClass);
        $this->assign('colBrakes',$colBreaks);
        $this->assign('limit',$limit);

        return $this->fetchView($this->getTemplateFile());
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    public function addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<ul class="level' . $childLevel . ' submenu">';
        $html .= $this->render($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';

        return $html;
    }
}