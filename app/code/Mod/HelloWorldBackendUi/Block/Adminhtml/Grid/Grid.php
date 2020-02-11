<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldBackendUi\Block\Adminhtml\Grid;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Mod\HelloWorldBackendUi\Model\ResourceModel\Grid\Grid\CollectionFactory as ExtraCommentCollectionFactory;
use Magento\Framework\App\Request\Http as Request;
use Mod\HelloWorldApi\Api\Data\ApprovedTypesInterface;

/**
 * Customer edit form extra comments grid block.
 */
class Grid extends Extended
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ExtraCommentCollectionFactory
     */
    private $extraCommentCollectionFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param ExtraCommentCollectionFactory $extraCommentCollectionFactory
     * @param Request $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ExtraCommentCollectionFactory $extraCommentCollectionFactory,
        Request $request,
        array $data = []
    ) {
        $this->extraCommentCollectionFactory = $extraCommentCollectionFactory;
        $this->request = $request;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Grid constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('index');
        $this->setDefaultSort('comment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection function.
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $extraCommentCollection = $this->extraCommentCollectionFactory->create()
            ->addFieldToSelect('*');
        $extraCommentCollection->addFieldToFilter('customer_id', $this->request->getParam('id'));
        $this->setCollection($extraCommentCollection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns function.
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'filter_index' => 'comment_id',
                'index' => 'comment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'is_approved',
            [
                'header' => __('Approved'),
                'type' => 'options',
                'options' => [
                    ApprovedTypesInterface::NO => 'No',
                    ApprovedTypesInterface::YES => 'Yes',
                ],
                'filter_index' => 'is_approved',
                'index' => 'is_approved',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'extra_comment',
            [
                'header' => __('Comment'),
                'type' => 'text',
                'filter_index' => 'extra_comment',
                'index' => 'extra_comment',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Actions'),
                'width' => '100px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Delete'),
                        'url' => ['base' => 'uigrid/index/delete'],
                        'field' => 'comment_id'
                    ],
                    [
                        'caption' => __('Approve'),
                        'url' => ['base' => 'uigrid/index/approve'],
                        'field' => 'comment_id'
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'comment_id',
                'is_system' => true,
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Grid url.
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/edit', ['_current' => true]);
    }
}
