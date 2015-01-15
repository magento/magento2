<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute;

class Delete extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be deleted.'));
                return $resultRedirect->setPath('catalog/*/');
            }

            try {
                $model->delete();
                $this->messageManager->addSuccess(__('The product attribute has been deleted.'));
                return $resultRedirect->setPath('catalog/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath(
                    'catalog/*/edit',
                    ['attribute_id' => $this->getRequest()->getParam('attribute_id')]
                );
            }
        }
        $this->messageManager->addError(__('We can\'t find an attribute to delete.'));
        return $resultRedirect->setPath('catalog/*/');
    }
}
