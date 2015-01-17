<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html\Link;

/**
 * Block representing link with two possible states.
 * "Current" state means link leads to URL equivalent to URL of currently displayed page.
 *
 * @method string                          getLabel()
 * @method string                          getPath()
 * @method string                          getTitle()
 * @method null|bool                       getCurrent()
 * @method \Magento\Framework\View\Element\Html\Link\Current setCurrent(bool $value)
 */
class Current extends \Magento\Framework\View\Element\Template
{
    /**
     * Default path
     *
     * @var \Magento\Framework\App\DefaultPathInterface
     */
    protected $_defaultPath;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_defaultPath = $defaultPath;
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath());
    }

    /**
     * Get current mca
     *
     * @return string
     */
    private function getMca()
    {
        $routeParts = [
            'module' => $this->_request->getModuleName(),
            'controller' => $this->_request->getControllerName(),
            'action' => $this->_request->getActionName(),
        ];

        $parts = [];
        foreach ($routeParts as $key => $value) {
            if (!empty($value) && $value != $this->_defaultPath->getPart($key)) {
                $parts[] = $value;
            }
        }
        return implode('/', $parts);
    }

    /**
     * Check if link leads to URL equivalent to URL of currently displayed page
     *
     * @return bool
     */
    public function isCurrent()
    {
        return $this->getCurrent() || $this->getUrl($this->getPath()) == $this->getUrl($this->getMca());
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

        $highlight = '';

        if ($this->getIsHighlighted()) {
            $highlight = ' current';
        }

        if ($this->isCurrent()) {
            $html = '<li class="nav item current">';
            $html .= '<strong>' . $this->escapeHtml(__($this->getLabel())) . '</strong>';
            $html .= '</li>';
        } else {
            $html = '<li class="nav item' . $highlight . '"><a href="' . $this->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle() ? ' title="' . $this->escapeHtml(__($this->getTitle())) . '"' : '';
            $html .= '>' . $this->escapeHtml(__($this->getLabel())) . '</a></li>';
        }

        return $html;
    }
}
