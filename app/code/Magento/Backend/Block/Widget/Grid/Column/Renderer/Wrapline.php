<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer line to wrap
 *
 * @api
 */
class Wrapline extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Default max length of a line at one row
     *
     * @var integer
     */
    protected $_defaultMaxLineLength = 60;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        array $data = []
    ) {
        $this->string = $string;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $line = parent::_getValue($row);
        $wrappedLine = '';
        $lineLength = $this->getColumn()->getData(
            'lineLength'
        ) ? $this->getColumn()->getData(
            'lineLength'
        ) : $this->_defaultMaxLineLength;
        for ($i = 0, $n = floor($this->string->strlen($line) / $lineLength); $i <= $n; $i++) {
            $wrappedLine .= $this->string->substr($line, $lineLength * $i, $lineLength) . "<br />";
        }
        return $wrappedLine;
    }
}
