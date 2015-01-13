<?php
/**
 * Renders HTML anchor or nothing depending on isVisible().
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\Object;

class Link extends AbstractRenderer
{
    /** @var \Magento\Framework\Object */
    protected $_row;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render grid row
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(Object $row)
    {
        $this->_row = $row;

        if (!$this->isVisible()) {
            return '';
        }

        $html = sprintf(
            '<a href="%s" %s>%s</a>',
            $this->_getUrl($row),
            $this->_getAttributesHtml(),
            $this->getCaption()
        );

        return $html;
    }

    /**
     * Decide whether anything should be rendered.
     *
     * @return bool
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * Decide whether action associated with the link is not available.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return false;
    }

    /**
     * Return URL pattern for action associated with the link e.g. "(star)(slash)(star)(slash)activate" ->
     * will be translated to http://.../admin/integration/activate/id/X
     *
     * @return string
     */
    public function getUrlPattern()
    {
        return $this->getColumn()->getUrlPattern();
    }

    /**
     * Caption for the link.
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->isDisabled() ? $this
            ->getColumn()
            ->getDisabledCaption() ?: $this
            ->getColumn()
            ->getCaption() : $this
            ->getColumn()
            ->getCaption();
    }

    /**
     * Return additional HTML parameters for tag, e.g. 'style'
     *
     * @return string
     */
    protected function _getAttributesHtml()
    {
        $html = [];

        foreach ($this->_getAttributes() as $key => $value) {
            if ($value === null || $value == '') {
                continue;
            }
            $html[] = sprintf('%s="%s"', $key, $this->escapeHtml($value));
        }

        return join(' ', $html);
    }

    /**
     * Return additional HTML attributes for the tag.
     *
     * @return array
     */
    protected function _getAttributes()
    {
        /** @var \Magento\Core\Helper\Data $helper */
        $helper = $this->_coreHelper;
        $attributes = ['title' => $this->getCaption()];

        foreach ($this->_getDataAttributes() as $key => $attr) {
            $attributes['data-' . $key] = is_scalar($attr) ? $attr : $helper->jsonEncode($attr);
        }

        return $attributes;
    }

    /**
     * Return HTML data attributes, which treated in special manner:
     * - prepended by "data-"
     * - JSON-encoded if necessary
     *
     * @return array
     */
    protected function _getDataAttributes()
    {
        return [];
    }

    /**
     * Render URL for current item.
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _getUrl(Object $row)
    {
        return $this->isDisabled($row) ? '#' : $this->getUrl($this->getUrlPattern(), ['id' => $row->getId()]);
    }
}
