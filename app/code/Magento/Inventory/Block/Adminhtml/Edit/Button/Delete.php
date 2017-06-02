<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace  Magento\Inventory\Block\Adminhtml\Edit\Button;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Context;
use Magento\Framework\UrlInterface;

/**
 * Class Delete
 * Configures "Delete" button on the Source Management edit form.
 */
class Delete implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param UrlInterface $urlBuilder
     * @param SourceRepositoryInterface $sourceRepository
     * @param Context $context
     */
    public function __construct(
        UrlInterface $urlBuilder,
        SourceRepositoryInterface $sourceRepository,
        Context $context
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->sourceRepository = $sourceRepository;
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        $data = [];
        // This part should be refactored. Implemented for test purposes.
        try {
            $sourceId = $this->context->getRequest()->getParam('id');
            $sourceId = $this->sourceRepository->get($sourceId)->getSourceId();

        } catch (NoSuchEntityException $exception) {
            $sourceId = null;
        }

        if ((bool)$sourceId) {
            $deleteUrl = $this->urlBuilder->getUrl('*/*/delete', ['id' => $sourceId]);
            $data = [
                'label' => __('Delete Entry'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $deleteUrl . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
