<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter templates grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Template;

use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Magento\Framework\App\TemplateTypesInterface;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Template\Collection
     */
    protected $_templateCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Newsletter\Model\ResourceModel\Template\Collection $templateCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Newsletter\Model\ResourceModel\Template\Collection $templateCollection,
        array $data = []
    ) {
        $this->_templateCollection = $templateCollection;
        parent::__construct($context, $backendHelper, $data);
        $this->setEmptyText(__('No Templates Found'));
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return WidgetGrid
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_templateCollection->useOnlyActual());

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'template_code',
            [
                'header' => __('ID'),
                'index' => 'template_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'code',
            [
                'header' => __('Template'),
                'index' => 'template_code',
                'header_css_class' => 'col-template',
                'column_css_class' => 'col-template'
            ]
        );

        $this->addColumn(
            'added_at',
            [
                'header' => __('Added'),
                'index' => 'added_at',
                'gmtoffset' => true,
                'type' => 'datetime',
                'header_css_class' => 'col-added col-date',
                'column_css_class' => 'col-added col-date'
            ]
        );

        $this->addColumn(
            'modified_at',
            [
                'header' => __('Updated'),
                'index' => 'modified_at',
                'gmtoffset' => true,
                'type' => 'datetime',
                'header_css_class' => 'col-updated col-date',
                'column_css_class' => 'col-updated col-date'
            ]
        );

        $this->addColumn(
            'subject',
            [
                'header' => __('Subject'),
                'index' => 'template_subject',
                'header_css_class' => 'col-subject',
                'column_css_class' => 'col-subject'
            ]
        );

        $this->addColumn(
            'sender',
            [
                'header' => __('Sender'),
                'index' => 'template_sender_email',
                'renderer' => 'Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer\Sender',
                'header_css_class' => 'col-sender',
                'column_css_class' => 'col-sender'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Template Type'),
                'index' => 'template_type',
                'type' => 'options',
                'options' => [
                    TemplateTypesInterface::TYPE_HTML => 'html',
                    TemplateTypesInterface::TYPE_TEXT => 'text',
                ],
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'template_id',
                'sortable' => false,
                'filter' => false,
                'no_link' => true,
                'renderer' => 'Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer\Action',
                'header_css_class' => 'col-actions',
                'column_css_class' => 'col-actions'
            ]
        );

        return $this;
    }

    /**
     * Get row url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
