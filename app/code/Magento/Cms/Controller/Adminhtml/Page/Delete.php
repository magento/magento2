<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Delete
 * @package Magento\Cms\Controller\Adminhtml\Page
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::page_delete';

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Cms\Api\PageRepositoryInterface $pageRepository
     */
    public function __construct(
        Action\Context $context,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository = null
    ) {
        $this->pageRepository = $pageRepository
            ?: ObjectManager::getInstance()->get(\Magento\Cms\Api\PageRepositoryInterface::class);
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $pageId = $this->getRequest()->getParam('page_id');
        $model = $this->prepareModel($pageId);

        if (!$model || ($pageId && $model->getId() != $pageId)) {
            $this->messageManager->addErrorMessage(__('This page no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        $title = $model->getTitle();
        try {
            $this->pageRepository->delete($model);
            $this->messageManager->addSuccessMessage(__('The page has been deleted.'));
            $this->_eventManager->dispatch(
                'adminhtml_cmspage_on_delete',
                ['title' => $title, 'status' => 'success']
            );
        } catch (LocalizedException $e) {
            $this->_eventManager->dispatch(
                'adminhtml_cmspage_on_delete',
                ['title' => $title, 'status' => 'fail']
            );
            $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            return $resultRedirect->setPath('*/*/edit', ['page_id' => $pageId]);
        } catch (\Exception $e) {
            $this->_eventManager->dispatch(
                'adminhtml_cmspage_on_delete',
                ['title' => $title, 'status' => 'fail']
            );
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the page.'));
            return $resultRedirect->setPath('*/*/edit', ['page_id' => $pageId]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Retrieve and prepare model.
     *
     * @param $pageId
     * @return bool|\Magento\Cms\Api\Data\PageInterface
     */
    private function prepareModel($pageId)
    {
        $model = false;
        if ($pageId) {
            try {
                $model = $this->pageRepository->getById($pageId);
            } catch (NoSuchEntityException $e) {
                $model = false;
            }
        }

        return $model;
    }
}
