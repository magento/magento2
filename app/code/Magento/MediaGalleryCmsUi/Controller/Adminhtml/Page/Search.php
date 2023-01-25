<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryCmsUi\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Controller to search pages for ui-select component
 */
class Search extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::page';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param JsonFactory $resultFactory
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Context $context
     */
    public function __construct(
        JsonFactory $resultFactory,
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Context $context
    ) {
        $this->resultJsonFactory = $resultFactory;
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
    }

    /**
     * Execute pages search.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $searchKey = $this->getRequest()->getParam('searchKey');
        $currentPage = (int) $this->getRequest()->getParam('page');
        $limit = (int) $this->getRequest()->getParam('limit');

        $searchResult = $this->pageRepository->getList(
            $this->searchCriteriaBuilder->addFilter('title', '%' . $searchKey . '%', 'like')
                ->setCurrentPage($currentPage)
                ->setPageSize($limit)
                ->create()
        );

        $options = [];
        foreach ($searchResult->getItems() as $page) {
            $id = $page->getId();
            $options[$id] = [
                'value' => $id,
                'label' => $page->getTitle(),
                'is_active' => $page->isActive(),
                'optgroup' => false
            ];
        }

        return $this->resultJsonFactory->create()->setData([
            'options' => $options,
            'total' => $searchResult->getTotalCount()
        ]);
    }
}
