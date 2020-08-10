<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryCmsUi\Controller\Adminhtml\Block;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Controller to search blocks for ui-select component
 */
class Search extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::block';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param JsonFactory $resultFactory
     * @param BlockRepositoryInterface $blockRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Context $context
     */
    public function __construct(
        JsonFactory $resultFactory,
        BlockRepositoryInterface $blockRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Context $context
    ) {
        $this->resultJsonFactory = $resultFactory;
        $this->blockRepository = $blockRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
    }

    /**
     * Execute pages search.
     *
     * @return ResultInterface
     */
    public function execute() : ResultInterface
    {
        $searchKey = $this->getRequest()->getParam('searchKey');
        $currentPage = (int) $this->getRequest()->getParam('page');
        $limit = (int) $this->getRequest()->getParam('limit');

        $searchResult = $this->blockRepository->getList(
            $this->searchCriteriaBuilder->addFilter('title', '%' . $searchKey . '%', 'like')
                ->setCurrentPage($currentPage)
                ->setPageSize($limit)
                ->create()
        );

        $options = [];
        foreach ($searchResult->getItems() as $block) {
            $id = $block->getId();
            $options[$id] = [
                'value' => $id,
                'label' => $block->getTitle(),
                'is_active' => $block->isActive(),
                'optgroup' => false
            ];
        }

        return $this->resultJsonFactory->create()->setData([
            'options' => $options,
            'total' => $searchResult->getTotalCount()
        ]);
    }
}
