<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

/**
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.1.0
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_dateFilter = $dateFilter;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Promo quote edit action
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create(\Magento\SalesRule\Model\Rule::class);

        $this->_coreRegistry->register(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE, $model);

        $resultPage = $this->resultPageFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('sales_rule/*');
                return;
            }
            $model->getConditions()->setFormName('sales_rule_form');
            $model->getConditions()->setJsFormObject(
                $model->getConditionsFieldSetId($model->getConditions()->getFormName())
            );
            $model->getActions()->setFormName('sales_rule_form');
            $model->getActions()->setJsFormObject(
                $model->getActionsFieldSetId($model->getActions()->getFormName())
            );

            $resultPage->getLayout()->getBlock('promo_sales_rule_edit_tab_coupons')->setCanShow(true);
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_initAction();

        $this->_addBreadcrumb($id ? __('Edit Rule') : __('New Rule'), $id ? __('Edit Rule') : __('New Rule'));

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? $model->getName() : __('New Cart Price Rule')
        );
        $this->_view->renderLayout();
    }
}
