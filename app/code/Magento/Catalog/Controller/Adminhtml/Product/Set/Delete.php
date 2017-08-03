<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\Set\Delete
 *
 * @since 2.0.0
 */
class Delete extends \Magento\Catalog\Controller\Adminhtml\Product\Set
{
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     * @since 2.0.0
     */
    protected $attributeSetRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        parent::__construct($context, $coreRegistry);
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $setId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->attributeSetRepository->deleteById($setId);
            $this->messageManager->addSuccess(__('The attribute set has been removed.'));
            $resultRedirect->setPath('catalog/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t delete this set right now.'));
            $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
        return $resultRedirect;
    }
}
