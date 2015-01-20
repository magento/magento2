<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter;

/**
 * Adminhtml newsletter queue grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Newsletter\Model\Resource\Queue\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Newsletter\Model\Resource\Queue\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Newsletter\Model\Resource\Queue\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('queueGrid');
        $this->setDefaultSort('start_at');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(__('No Newsletter Found'));
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('customer/*/newsletter', ['_current' => true]);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Magento\Newsletter\Model\Resource\Queue\Collection */
        $collection = $this->_collectionFactory->create()->addTemplateInfo()->addSubscriberFilter(
            $this->_coreRegistry->registry('subscriber')->getId()
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'queue_id',
            ['header' => __('ID'), 'align' => 'left', 'index' => 'queue_id', 'width' => 10]
        );

        $this->addColumn(
            'start_at',
            [
                'header' => __('Start date'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'queue_start_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'finish_at',
            [
                'header' => __('End Date'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'queue_finish_at',
                'gmtoffset' => true,
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'letter_sent_at',
            [
                'header' => __('Receive Date'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'letter_sent_at',
                'gmtoffset' => true,
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'template_subject',
            ['header' => __('Subject'), 'align' => 'center', 'index' => 'template_subject']
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'center',
                'filter' => 'Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Filter\Status',
                'index' => 'queue_status',
                'renderer' => 'Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Renderer\Status'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Renderer\Action'
            ]
        );

        return parent::_prepareColumns();
    }
}
