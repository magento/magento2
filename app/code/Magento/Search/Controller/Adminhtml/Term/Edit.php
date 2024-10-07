<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Search\Controller\Adminhtml\Term as TermController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;
use Magento\Search\Model\Query as ModelQuery;

class Edit extends TermController implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param Registry $coreRegistry Core registry
     */
    public function __construct(
        Context $context,
        protected readonly Registry $coreRegistry
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create(ModelQuery::class);

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This search no longer exists.'));
                /** @var ResultRedirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('search/*');
                return $resultRedirect;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get(BackendSession::class)->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register('current_catalog_search', $model);

        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Search Terms'));
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getQueryText() : __('New Search'));
        $resultPage->getLayout()->getBlock('adminhtml.search.term.edit')
            ->setData('action', $this->getUrl('search/term/save'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Search') : __('New Search'),
            $id ? __('Edit Search') : __('New Search')
        );
        return $resultPage;
    }
}
