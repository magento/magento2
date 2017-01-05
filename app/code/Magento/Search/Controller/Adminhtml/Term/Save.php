<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Controller\Adminhtml\Term as TermController;
use Magento\Framework\Exception\LocalizedException;

class Save extends TermController
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @param Context $context
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory
    ) {
        parent::__construct($context);
        $this->queryFactory = $queryFactory;
    }

    /**
     * Save search query
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($this->getRequest()->isPost() && $data) {
            try {
                $model = $this->loadQuery();
                $model->addData($data);
                $model->setIsProcessed(0);
                $model->save();
                $this->messageManager->addSuccess(__('You saved the search term.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->proceedToEdit($data);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the search query.'));
                return $this->proceedToEdit($data);
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $redirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $redirectResult->setPath('search/*');
    }

    /**
     * Create\Load Query model instance
     *
     * @return \Magento\Search\Model\Query
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function loadQuery()
    {
        //validate query
        $queryText = $this->getRequest()->getPost('query_text', false);
        $queryId = $this->getRequest()->getPost('query_id', null);

        /* @var $model \Magento\Search\Model\Query */
        $model = $this->queryFactory->create();
        if ($queryText) {
            $storeId = $this->getRequest()->getPost('store_id', false);
            $model->setStoreId($storeId);
            $model->loadByQueryText($queryText);
            if ($model->getId() && $model->getId() != $queryId) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You already have an identical search term query.')
                );
            }
        }
        if ($queryId && !$model->getId()) {
            $model->load($queryId);
        }
        return $model;
    }

    /**
     * Redirect to Edit page
     *
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    private function proceedToEdit($data)
    {
        $this->_getSession()->setPageData($data);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $redirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $redirectResult->setPath('search/*/edit', ['id' => $this->getRequest()->getPost('query_id', null)]);
    }
}
