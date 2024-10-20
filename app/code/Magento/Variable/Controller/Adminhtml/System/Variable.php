<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Variable\Model\Variable as ModelVariable;

/**
 * Custom Variables admin controller
 */
abstract class Variable extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Variable::variable';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        protected readonly ForwardFactory $resultForwardFactory,
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly PageFactory $resultPageFactory,
        protected readonly LayoutFactory $layoutFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize Layout and set breadcrumbs
     *
     * @return Page
     */
    protected function createPage()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Variable::system_variable')
            ->addBreadcrumb(__('Custom Variables'), __('Custom Variables'));
        return $resultPage;
    }

    /**
     * Initialize Variable object
     *
     * @return ModelVariable
     */
    protected function _initVariable()
    {
        $variableId = $this->getRequest()->getParam('variable_id', null);
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        /* @var ModelVariable $variable */
        $variable = $this->_objectManager->create(ModelVariable::class);
        if ($variableId) {
            $variable->setStoreId($storeId)->load($variableId);
        }
        $this->_coreRegistry->register('current_variable', $variable);
        return $variable;
    }
}
