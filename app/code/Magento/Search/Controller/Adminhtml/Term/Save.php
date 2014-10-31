<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Search\Controller\Adminhtml\Term;

class Save extends \Magento\Search\Controller\Adminhtml\Term
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Save search query
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $hasError = false;
        $data = $this->getRequest()->getPost();
        $queryId = $this->getRequest()->getPost('query_id', null);
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirectResult = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost() && $data) {
            /* @var $model \Magento\Search\Model\Query */
            $model = $this->_objectManager->create('Magento\Search\Model\Query');

            // validate query
            $queryText = $this->getRequest()->getPost('query_text', false);
            $storeId = $this->getRequest()->getPost('store_id', false);

            try {
                if ($queryText) {
                    $model->setStoreId($storeId);
                    $model->loadByQueryText($queryText);
                    if ($model->getId() && $model->getId() != $queryId) {
                        throw new \Magento\Framework\Model\Exception(
                            __('You already have an identical search term query.')
                        );
                    } elseif (!$model->getId() && $queryId) {
                        $model->load($queryId);
                    }
                } else if ($queryId) {
                    $model->load($queryId);
                }

                $model->addData($data);
                $model->setIsProcessed(0);
                $model->save();
                $this->messageManager->addSuccess(__('You saved the search term.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $hasError = true;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the search query.'));
                $hasError = true;
            }
        }

        if ($hasError) {
            $this->_getSession()->setPageData($data);
            return $redirectResult->setPath('search/*/edit', ['id' => $queryId]);
        } else {
            return $redirectResult->setPath('search/*');
        }
    }
}
