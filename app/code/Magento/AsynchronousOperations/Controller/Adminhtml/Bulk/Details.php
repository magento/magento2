<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Controller\Adminhtml\Bulk;

use Magento\AsynchronousOperations\Model\AccessValidator;
use Magento\AsynchronousOperations\Model\IsAllowedForBulkUuid;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class View Operation Details Controller
 */
class Details extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var AccessValidator
     * @deprecated
     */
    private $accessValidator;

    /**
     * @var string
     */
    private $menuId;

    /**
     * @var IsAllowedForBulkUuid
     */
    private $isAllowedForBulkUuid;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AccessValidator $accessValidator
     * @param string $menuId
     * @param IsAllowedForBulkUuid|null $isAllowedForBulkUuid
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AccessValidator $accessValidator,
        $menuId = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations',
        ?IsAllowedForBulkUuid $isAllowedForBulkUuid = null
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accessValidator = $accessValidator;
        $this->menuId = $menuId;
        $this->isAllowedForBulkUuid = $isAllowedForBulkUuid
            ?: ObjectManager::getInstance()->get(IsAllowedForBulkUuid::class);
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->isAllowedForBulkUuid->execute($this->getRequest()->getParam('uuid'));
    }

    /**
     * Bulk details action
     *
     * @return Page
     */
    public function execute()
    {
        $bulkId = $this->getRequest()->getParam('uuid');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->initLayout();
        $this->_setActiveMenu($this->menuId);
        $resultPage->getConfig()->getTitle()->prepend(__('Action Details - #' . $bulkId));

        return $resultPage;
    }
}
