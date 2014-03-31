<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Index
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Index\Block\Adminhtml\Process;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Process model
     *
     * @var \Magento\Index\Model\Process
     */
    protected $_indexProcess;

    /**
     * Mass-action block
     *
     * @var string
     */
    protected $_massactionBlockName = 'Magento\Index\Block\Adminhtml\Process\Grid\Massaction';

    /**
     * Event repository
     *
     * @var \Magento\Index\Model\EventRepository
     */
    protected $_eventRepository;

    /**
     * @var \Magento\Index\Model\Resource\Process\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Index\Model\Resource\Process\CollectionFactory $factory
     * @param \Magento\Index\Model\Process $indexProcess
     * @param \Magento\Index\Model\EventRepository $eventRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Index\Model\Resource\Process\CollectionFactory $factory,
        \Magento\Index\Model\Process $indexProcess,
        \Magento\Index\Model\EventRepository $eventRepository,
        array $data = array()
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_eventRepository = $eventRepository;
        $this->_indexProcess = $indexProcess;
        $this->_collectionFactory = $factory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('indexer_processes_grid');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    /**
     * Prepare grid collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_collectionFactory->create());
        parent::_prepareCollection();

        return $this;
    }

    /**
     * Add name and description to collection elements
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        /** @var $item \Magento\Index\Model\Process */
        foreach ($this->_collection as $key => $item) {
            if (!$item->getIndexer()->isVisible()) {
                $this->_collection->removeItemByKey($key);
                continue;
            }
            $item->setName($item->getIndexer()->getName());
            $item->setDescription($item->getIndexer()->getDescription());
            $item->setUpdateRequired($this->_eventRepository->hasUnprocessed($item) ? 1 : 0);
            if ($item->isLocked()) {
                $item->setStatus(\Magento\Index\Model\Process::STATUS_RUNNING);
            }
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'indexer_code',
            array('header' => __('Index'), 'width' => '180', 'align' => 'left', 'index' => 'name', 'sortable' => false)
        );

        $this->addColumn(
            'description',
            array('header' => __('Description'), 'align' => 'left', 'index' => 'description', 'sortable' => false)
        );

        $this->addColumn(
            'mode',
            array(
                'header' => __('Mode'),
                'width' => '150',
                'align' => 'left',
                'index' => 'mode',
                'type' => 'options',
                'options' => $this->_indexProcess->getModesOptions()
            )
        );

        $this->addColumn(
            'status',
            array(
                'header' => __('Status'),
                'width' => '120',
                'align' => 'left',
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_indexProcess->getStatusesOptions(),
                'frame_callback' => array($this, 'decorateStatus')
            )
        );

        $this->addColumn(
            'update_required',
            array(
                'header' => __('Update Required'),
                'sortable' => false,
                'width' => '120',
                'align' => 'left',
                'index' => 'update_required',
                'type' => 'options',
                'options' => $this->_indexProcess->getUpdateRequiredOptions(),
                'frame_callback' => array($this, 'decorateUpdateRequired')
            )
        );

        $this->addColumn(
            'ended_at',
            array(
                'header' => __('Updated'),
                'type' => 'datetime',
                'width' => '180',
                'align' => 'left',
                'index' => 'ended_at',
                'frame_callback' => array($this, 'decorateDate')
            )
        );

        $this->addColumn(
            'action',
            array(
                'header' => __('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Reindex Data'),
                        'url' => array('base' => '*/*/reindexProcess'),
                        'field' => 'process'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'is_system' => true
            )
        );

        parent::_prepareColumns();

        return $this;
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param \Magento\Index\Model\Process $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     *
     * @return string
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getStatus()) {
            case \Magento\Index\Model\Process::STATUS_PENDING:
                $class = 'grid-severity-notice';
                break;
            case \Magento\Index\Model\Process::STATUS_RUNNING:
                $class = 'grid-severity-major';
                break;
            case \Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX:
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="' . $class . '"><span>' . $value . '</span></span>';
    }

    /**
     * Decorate "Update Required" column values
     *
     * @param string $value
     * @param \Magento\Index\Model\Process $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     *
     * @return string
     */
    public function decorateUpdateRequired($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getUpdateRequired()) {
            case 0:
                $class = 'grid-severity-notice';
                break;
            case 1:
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="' . $class . '"><span>' . $value . '</span></span>';
    }

    /**
     * Decorate last run date coumn
     *
     * @param string $value
     * @param \Magento\Index\Model\Process $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     *
     * @return string
     */
    public function decorateDate($value, $row, $column, $isExport)
    {
        if (!$value) {
            return __('Never');
        }
        return $value;
    }

    /**
     * Get row edit url
     *
     * @param \Magento\Index\Model\Process $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/*/edit', array('process' => $row->getId()));
    }

    /**
     * Add mass-actions to grid
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('process_id');
        $this->getMassactionBlock()->setFormFieldName('process');

        $modeOptions = $this->_indexProcess->getModesOptions();

        $this->getMassactionBlock()->addItem(
            'change_mode',
            array(
                'label' => __('Change Index Mode'),
                'url' => $this->getUrl('adminhtml/*/massChangeMode'),
                'additional' => array(
                    'mode' => array(
                        'name' => 'index_mode',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Index mode'),
                        'values' => $modeOptions
                    )
                )
            )
        );

        $this->getMassactionBlock()->addItem(
            'reindex',
            array('label' => __('Reindex Data'), 'url' => $this->getUrl('adminhtml/*/massReindex'), 'selected' => true)
        );

        return $this;
    }
}
