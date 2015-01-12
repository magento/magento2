<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Store controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Store extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->filterManager = $filterManager;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system_store')
            ->addBreadcrumb(__('System'), __('System'))
            ->addBreadcrumb(__('Manage Stores'), __('Manage Stores'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Adminhtml::store');
    }

    /**
     * Backup database
     *
     * @return bool
     */
    protected function _backupDatabase()
    {
        if (!$this->getRequest()->getParam('create_backup')) {
            return true;
        }
        try {
            /** @var \Magento\Backup\Model\Db $backupDb */
            $backupDb = $this->_objectManager->create('Magento\Backup\Model\Db');
            /** @var \Magento\Backup\Model\Backup $backup */
            $backup = $this->_objectManager->create('Magento\Backup\Model\Backup');
            /** @var Filesystem $filesystem */
            $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
            $backup->setTime(time())
                ->setType('db')
                ->setPath($filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('backups'));
            $backupDb->createBackup($backup);
            $this->messageManager->addSuccess(__('The database was backed up.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('We couldn\'t create a backup right now. Please try again later.')
            );
            return false;
        }
        return true;
    }

    /**
     * Add notification on deleting store / store view / website
     *
     * @param string $typeTitle
     * @return $this
     */
    protected function _addDeletionNotice($typeTitle)
    {
        $this->messageManager->addNotice(
            __(
                'Deleting a %1 will not delete the information associated with the %1 (e.g. categories, products, etc.), but the %1 will not be able to be restored. It is suggested that you create a database backup before deleting the %1.',
                $typeTitle
            )
        );
        return $this;
    }
}
