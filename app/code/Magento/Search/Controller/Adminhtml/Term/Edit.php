<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Search\Controller\Adminhtml\Search;

class Edit extends \Magento\Search\Controller\Adminhtml\Term
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magento\Search\Model\Query');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This search no longer exists.'));
                $this->_redirect('search/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('current_catalog_search', $model);

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
