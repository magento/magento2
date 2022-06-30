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
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Controller to get selected page for ui-select component
 */
class GetSelected extends Action implements HttpGetActionInterface
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
     * @param JsonFactory $resultFactory
     * @param PageRepositoryInterface $pageRepository
     * @param Context $context
     */
    public function __construct(
        JsonFactory $resultFactory,
        PageRepositoryInterface $pageRepository,
        Context $context
    ) {
        $this->resultJsonFactory = $resultFactory;
        $this->pageRepository = $pageRepository;
        parent::__construct($context);
    }

    /**
     * Return selected pages options.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $options = [];
        $pageIds = $this->getRequest()->getParam('ids');

        if (!is_array($pageIds)) {
            return $this->resultJsonFactory->create()->setData('parameter ids must be type of array');
        }
        foreach ($pageIds as $id) {
            try {
                $page = $this->pageRepository->getById($id);
                $options[] = [
                    'value' => $page->getId(),
                    'label' => $page->getTitle(),
                    'is_active' => $page->isActive(),
                    'optgroup' => false
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this->resultJsonFactory->create()->setData($options);
    }
}
