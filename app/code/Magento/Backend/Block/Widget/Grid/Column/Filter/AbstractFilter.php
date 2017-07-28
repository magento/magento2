<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Grid column filter block
 *
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @since 2.0.0
 */
class AbstractFilter extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Backend\Block\Widget\Grid\Column\Filter\FilterInterface
{
    /**
     * Column related to filter
     *
     * @var \Magento\Backend\Block\Widget\Grid\Column
     * @since 2.0.0
     */
    protected $_column;

    /**
     * @var \Magento\Framework\DB\Helper
     * @since 2.0.0
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        array $data = []
    ) {
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Set column related to filter
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
     * @since 2.0.0
     */
    public function setColumn($column)
    {
        $this->_column = $column;
        return $this;
    }

    /**
     * Retrieve column related to filter
     *
     * @return \Magento\Backend\Block\Widget\Grid\Column
     * @since 2.0.0
     */
    public function getColumn()
    {
        return $this->_column;
    }

    /**
     * Retrieve html name of filter
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getHtmlName()
    {
        return $this->escapeHtml($this->getColumn()->getId());
    }

    /**
     * Retrieve html id of filter
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getHtmlId()
    {
        return $this->escapeHtml($this->getColumn()->getHtmlId());
    }

    /**
     * Retrieve escaped value
     *
     * @param mixed $index
     * @return string
     * @since 2.0.0
     */
    public function getEscapedValue($index = null)
    {
        return $this->escapeHtml((string)$this->getValue($index));
    }

    /**
     * Retrieve condition
     *
     * @return array
     * @since 2.0.0
     */
    public function getCondition()
    {
        $likeExpression = $this->_resourceHelper->addLikeEscape($this->getValue(), ['position' => 'any']);
        return ['like' => $likeExpression];
    }

    /**
     * Retrieve filter html
     *
     * @return string
     * @since 2.0.0
     */
    public function getHtml()
    {
        return '';
    }
}
