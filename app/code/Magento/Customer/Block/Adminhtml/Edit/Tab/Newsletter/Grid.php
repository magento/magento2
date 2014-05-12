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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        array $data = array()
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
        return $this->getUrl('customer/*/newsletter', array('_current' => true));
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
            array('header' => __('ID'), 'align' => 'left', 'index' => 'queue_id', 'width' => 10)
        );

        $this->addColumn(
            'start_at',
            array(
                'header' => __('Start date'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'queue_start_at',
                'default' => ' ---- '
            )
        );

        $this->addColumn(
            'finish_at',
            array(
                'header' => __('End Date'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'queue_finish_at',
                'gmtoffset' => true,
                'default' => ' ---- '
            )
        );

        $this->addColumn(
            'letter_sent_at',
            array(
                'header' => __('Receive Date'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'letter_sent_at',
                'gmtoffset' => true,
                'default' => ' ---- '
            )
        );

        $this->addColumn(
            'template_subject',
            array('header' => __('Subject'), 'align' => 'center', 'index' => 'template_subject')
        );

        $this->addColumn(
            'status',
            array(
                'header' => __('Status'),
                'align' => 'center',
                'filter' => 'Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Filter\Status',
                'index' => 'queue_status',
                'renderer' => 'Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Renderer\Status'
            )
        );

        $this->addColumn(
            'action',
            array(
                'header' => __('Action'),
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Renderer\Action'
            )
        );

        return parent::_prepareColumns();
    }
}
