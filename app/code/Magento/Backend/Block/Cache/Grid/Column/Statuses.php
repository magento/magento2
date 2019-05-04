<?php
/**
 * Status column for Cache grid
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Cache\Grid\Column;

/**
 * @api
 * @since 100.0.2
 */
class Statuses extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_cacheTypeList = $cacheTypeList;
    }

    /**
     * Add to column decorated status
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateStatus'];
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param  \Magento\Framework\Model\AbstractModel $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $invalidedTypes = $this->_cacheTypeList->getInvalidated();
        if (isset($invalidedTypes[$row->getId()])) {
            $cell = '<span class="grid-severity-minor"><span>' . __('Invalidated') . '</span></span>';
        } else {
            if ($row->getStatus()) {
                $cell = '<span class="grid-severity-notice"><span>' . $value . '</span></span>';
            } else {
                $cell = '<span class="grid-severity-critical"><span>' . $value . '</span></span>';
            }
        }
        return $cell;
    }
}
