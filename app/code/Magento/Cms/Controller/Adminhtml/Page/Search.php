<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Controller for CMS page search AJAX requests
 */
class Search extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Cms::page';

    /**
     * @var int
     */
    private const SEARCH_LIMIT = 50;

    /**
     * @param Context $context
     * @param PageCollectionFactory $pageCollectionFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        private readonly PageCollectionFactory $pageCollectionFactory,
        private readonly JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
    }

    /**
     * AJAX action to search and return CMS pages
     *
     * @return Json
     */
    public function execute(): Json
    {
        $searchTerm = $this->getRequest()->getParam('label_part', '');
        
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToSelect(['page_id', 'title', 'identifier']);
        
        if ($searchTerm !== '') {
            $collection->addFieldToFilter(
                ['title', 'identifier'],
                [
                    ['like' => '%' . $searchTerm . '%'],
                    ['like' => '%' . $searchTerm . '%']
                ]
            );
        }
        
        $collection->setPageSize(self::SEARCH_LIMIT);
        $collection->setCurPage(1);

        $options = [];
        foreach ($collection as $page) {
            $options[] = [
                'id' => $page->getIdentifier(),
                'label' => $page->getTitle() . ' (ID: ' . $page->getId() . ')'
            ];
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData($options);
    }
}
