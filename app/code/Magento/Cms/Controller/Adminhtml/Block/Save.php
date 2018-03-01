<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Block;

use Magento\Backend\App\Action;
use Magento\Cms\Model\Block;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Save
 * @package Magento\Cms\Controller\Adminhtml\Block
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     *
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        \Magento\Cms\Model\BlockFactory $blockFactory = null,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository = null
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->blockFactory = $blockFactory
            ?: ObjectManager::getInstance()->get(\Magento\Cms\Model\BlockFactory::class);
        $this->blockRepository = $blockRepository
            ?: ObjectManager::getInstance()->get(\Magento\Cms\Api\BlockRepositoryInterface::class);
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('block_id');
            $model = $this->prepareModel($data);

            if (!$model || ($id && $model->getId() != $id)) {
                $this->messageManager->addErrorMessage(__('This block no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $this->_eventManager->dispatch(
                'cms_block_prepare_save',
                ['block' => $model, 'request' => $this->getRequest()]
            );

            try {
                $this->blockRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the block.'));
                $this->dataPersistor->clear('cms_block');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['block_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the block.'));
            }

            $this->dataPersistor->set('cms_block', $model->getData());
            return $resultRedirect->setPath('*/*/edit', ['block_id' => $this->getRequest()->getParam('block_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Avoid NPath complexity, retrieve and prepare model.
     *
     * @param $data
     * @return bool|\Magento\Cms\Api\Data\BlockInterface
     */
    private function prepareModel($data)
    {
        $id = $this->getRequest()->getParam('block_id');
        if ($id) {
            try {
                $model = $this->blockRepository->getById($id);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        if (!isset($model)) {
            $model = $this->blockFactory->create();
        }

        if (isset($data['is_active']) && $data['is_active'] === 'true') {
            $data['is_active'] = Block::STATUS_ENABLED;
        }
        if (empty($data['block_id'])) {
            $data['block_id'] = null;
        }

        $model->setData($data);

        return $model;
    }
}
