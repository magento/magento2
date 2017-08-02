<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard\Searches\Renderer;

/**
 * Dashboard search query column renderer
 * @api
 * @since 2.0.0
 */
class Searchquery extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * String helper
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.0.0
     */
    protected $stringHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        array $data = []
    ) {
        $this->stringHelper = $stringHelper;
        parent::__construct($context, $data);
    }

    /**
     * Renders a column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if ($this->stringHelper->strlen($value) > 30) {
            $value = '<span title="' . $this->escapeHtml(
                $value
            ) . '">' . $this->escapeHtml(
                $this->filterManager->truncate($value, ['length' => 30])
            ) . '</span>';
        } else {
            $value = $this->escapeHtml($value);
        }
        return $value;
    }
}
