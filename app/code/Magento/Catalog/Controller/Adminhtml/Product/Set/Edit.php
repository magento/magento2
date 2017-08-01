<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\Set\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\Catalog\Controller\Adminhtml\Product\Set
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_setTypeId();
        $attributeSet = $this->_objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
            ->load($this->getRequest()->getParam('id'));

        if (!$attributeSet->getId()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/*/index');
        }

        $this->_coreRegistry->register('current_attribute_set', $attributeSet);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Catalog::catalog_attributes_sets');
        $resultPage->getConfig()->getTitle()->prepend(__('Attribute Sets'));
        $resultPage->getConfig()->getTitle()->prepend(
            $attributeSet->getId() ? $attributeSet->getAttributeSetName() : __('New Set')
        );
        $resultPage->addBreadcrumb(__('Catalog'), __('Catalog'));
        $resultPage->addBreadcrumb(__('Manage Product Sets'), __('Manage Product Sets'));
        return $resultPage;
    }
}
