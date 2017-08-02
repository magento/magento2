<?php
/**
 * Renders HTML anchor or nothing depending on isVisible().
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link
 *
 * @since 2.0.0
 */
class Link extends AbstractRenderer
{
    /**
     * @var \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected $_row;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     * @since 2.0.0
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render grid row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    public function render(DataObject $row)
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
     * @since 2.0.0
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * Decide whether action associated with the link is not available.
     *
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getUrlPattern()
    {
        return $this->getColumn()->getUrlPattern();
    }

    /**
     * Caption for the link.
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getAttributesHtml()
    {
        $html = [];

        foreach ($this->_getAttributes() as $key => $value) {
            if ($value === null || $value == '') {
                continue;
            }
            $html[] = sprintf('%s="%s"', $key, $this->escapeHtmlAttr($value, false));
        }

        return join(' ', $html);
    }

    /**
     * Return additional HTML attributes for the tag.
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getAttributes()
    {
        /** @var \Magento\Framework\Json\Helper\Data $helper */
        $helper = $this->jsonHelper;
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
     * @since 2.0.0
     */
    protected function _getDataAttributes()
    {
        return [];
    }

    /**
     * Render URL for current item.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    protected function _getUrl(DataObject $row)
    {
        return $this->isDisabled($row) ? '#' : $this->getUrl($this->getUrlPattern(), ['id' => $row->getId()]);
    }
}
