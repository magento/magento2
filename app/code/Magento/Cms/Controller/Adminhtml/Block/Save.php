<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Block;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Block;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;

class Save extends \Magento\Cms\Controller\Adminhtml\Block
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('block_id');

            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Block::STATUS_ENABLED;
            }
            if (empty($data['block_id'])) {
                $data['block_id'] = null;
            }

            /** @var \Magento\Cms\Model\Block $model */
            $model = $this->_objectManager->create('Magento\Cms\Model\Block')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This block no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the block.'));
                $this->dataPersistor->clear('cms_block');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['block_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the block.'));
            }

            $this->dataPersistor->set('cms_block', $data);
            return $resultRedirect->setPath('*/*/edit', ['block_id' => $this->getRequest()->getParam('block_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
