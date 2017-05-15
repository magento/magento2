<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Grid column filter block
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

class AbstractFilter extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Backend\Block\Widget\Grid\Column\Filter\FilterInterface
{
    /**
     * Column related to filter
     *
     * @var \Magento\Backend\Block\Widget\Grid\Column
     */
    protected $_column;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param array $data
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
     */
    public function getColumn()
    {
        return $this->_column;
    }

    /**
     * Retrieve html name of filter
     *
     * @return string
     */
    protected function _getHtmlName()
    {
        return $this->escapeHtml($this->getColumn()->getId());
    }

    /**
     * Retrieve html id of filter
     *
     * @return string
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
     */
    public function getEscapedValue($index = null)
    {
        return $this->escapeHtml((string)$this->getValue($index));
    }

    /**
     * Retrieve condition
     *
     * @return array
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
     */
    public function getHtml()
    {
        return '';
    }
}
