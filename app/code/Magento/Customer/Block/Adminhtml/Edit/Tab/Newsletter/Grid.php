<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Renderer\Action;
use Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Renderer\Status;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Adminhtml newsletter queue grid block
 *
 * @api
 * @since 100.0.2
 */
class Grid extends Extended
{
    /**
     * Core registry
     *
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param Registry $coreRegistry
     * @param array $data
     * @param Share|null $shareConfig
     * @param SystemStore|null $systemStore
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Registry $coreRegistry,
        array $data = [],
        ?Share $shareConfig = null,
        ?SystemStore $systemStore = null
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->shareConfig = $shareConfig ?? ObjectManager::getInstance()->get(Share::class);
        $this->systemStore = $systemStore ?? ObjectManager::getInstance()->get(SystemStore::class);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('customer/*/newsletter', ['_current' => true]);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $customerId = $this->getCurrentCustomerId();
        $collection = $this->_collectionFactory->create()->addTemplateInfo()->addCustomerFilter($customerId);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
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

        if ($this->isMultiplyWebsiteMode()) {
            $this->addColumn(
                'store_view',
                [
                    'header' => __('Store View'),
                    'align' => 'center',
                    'index' => 'subscriber_store_id',
                    'type' => 'options',
                    'option_groups' => $this->systemStore->getStoreValuesForForm(),
                ]
            );
        }

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'center',
                'filter' => \Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Filter\Status::class,
                'index' => 'queue_status',
                'renderer' => Status::class
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => Action::class
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get current customer id
     *
     * @return int
     */
    private function getCurrentCustomerId(): int
    {
        return (int)$this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Is multiply website mode
     *
     * @return bool
     */
    private function isMultiplyWebsiteMode(): bool
    {
        return $this->shareConfig->isGlobalScope()
            && count($this->_storeManager->getWebsites()) > 1;
    }
}
