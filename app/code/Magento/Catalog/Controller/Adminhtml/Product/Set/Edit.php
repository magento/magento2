<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Controller\Adminhtml\Product\Set;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Edit attribute set controller.
 */
class Edit extends Set implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_setTypeId();
        $attributeSet = $this->attributeSetRepository->get($this->getRequest()->getParam('id'));
        if (!$attributeSet->getId()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/*/index');
        }
        $this->_coreRegistry->register('current_attribute_set', $attributeSet);

        /** @var Page $resultPage */
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
