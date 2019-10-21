<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Template;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Sitemap\Controller\Adminhtml\Sitemap;

/**
 * Controller class Edit. Responsible for rendering of a sitemap edit page
 */
class Edit extends Sitemap implements HttpGetActionInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(Context $context, Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Edit sitemap
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('sitemap_id');
        $model = $this->_objectManager->create(\Magento\Sitemap\Model\Sitemap::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This sitemap no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get(Session::class)->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('sitemap_sitemap', $model);

        // 5. Build edit form
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Sitemap') : __('New Sitemap'),
            $id ? __('Edit Sitemap') : __('New Sitemap')
        )->_addContent(
            $this->_view->getLayout()->createBlock(\Magento\Sitemap\Block\Adminhtml\Edit::class)
        )->_addJs(
            $this->_view->getLayout()->createBlock(Template::class)->setTemplate('Magento_Sitemap::js.phtml')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Site Map'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getSitemapFilename() : __('New Site Map')
        );
        $this->_view->renderLayout();
    }
}
