<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Magento\Framework\View\Result\PageFactory;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote as AdminhtmlPromoQuote;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\Rule;

class Edit extends AdminhtmlPromoQuote implements HttpGetActionInterface
{
    /**
     * @param ActionContext $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param DateFilter $dateFilter
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        ActionContext $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        DateFilter $dateFilter,
        protected readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_dateFilter = $dateFilter;
    }

    /**
     * Promo quote edit action
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create(Rule::class);

        $this->_coreRegistry->register(RegistryConstants::CURRENT_SALES_RULE, $model);

        $resultPage = $this->resultPageFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
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
        $data = $this->_objectManager->get(BackendSession::class)->getPageData(true);
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
