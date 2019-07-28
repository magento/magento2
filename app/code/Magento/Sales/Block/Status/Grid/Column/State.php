<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Status\Grid\Column;

/**
 * @api
 * @since 100.0.2
 */
class State extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_config;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\Order\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\Order\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_config = $config;
    }

    /**
     * Add decorated status to column
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateState'];
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param \Magento\Sales\Model\Order\Status $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateState($value, $row, $column, $isExport)
    {
        $status = $row->getStatus();
        if ($value) {
            $cell = $value . '[' . $this->_config->getStateLabelByStateAndStatus($value, $status) . ']';
        } else {
            $cell = $value;
        }
        return $cell;
    }
}
