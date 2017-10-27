<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Block;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Delete
 * @package Magento\Cms\Controller\Adminhtml\Block
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::block';

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        Action\Context $context,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository = null
    ) {
        $this->blockRepository = $blockRepository
            ?: ObjectManager::getInstance()->get(\Magento\Cms\Api\BlockRepositoryInterface::class);
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

        $blockId = $this->getRequest()->getParam('block_id');
        $model = $this->prepareModel($blockId);

        if (!$model || ($blockId && $model->getId() != $blockId)) {
            $this->messageManager->addErrorMessage(__('This block no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        $title = $model->getTitle();
        try {
            $this->blockRepository->delete($model);
            $this->messageManager->addSuccessMessage(__('The block has been deleted.'));
            $this->_eventManager->dispatch(
                'adminhtml_cmsblock_on_delete',
                ['title' => $title, 'status' => 'success']
            );
        } catch (LocalizedException $e) {
            $this->_eventManager->dispatch(
                'adminhtml_cmsblock_on_delete',
                ['title' => $title, 'status' => 'fail']
            );
            $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            return $resultRedirect->setPath('*/*/edit', ['block_id' => $blockId]);
        } catch (\Exception $e) {
            $this->_eventManager->dispatch(
                'adminhtml_cmsblock_on_delete',
                ['title' => $title, 'status' => 'fail']
            );
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the block.'));
            return $resultRedirect->setPath('*/*/edit', ['block_id' => $blockId]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Retrieve and prepare model.
     *
     * @param $blockId
     * @return bool|\Magento\Cms\Api\Data\BlockInterface
     */
    private function prepareModel($blockId)
    {
        $model = false;
        if ($blockId) {
            try {
                $model = $this->blockRepository->getById($blockId);
            } catch (NoSuchEntityException $e) {
                $model = false;
            }
        }

        return $model;
    }
}
