<?php
/**
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
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;

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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->filterManager = $filterManager;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Backend::system_store'
        )->_addBreadcrumb(
            __('System'),
            __('System')
        )->_addBreadcrumb(
            __('Manage Stores'),
            __('Manage Stores')
        );
        return $this;
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
     * @param string $failPath redirect path if backup failed
     * @param array $arguments
     * @return $this|void
     */
    protected function _backupDatabase($failPath, $arguments = array())
    {
        if (!$this->getRequest()->getParam('create_backup')) {
            return $this;
        }
        try {
            $backupDb = $this->_objectManager->create('Magento\Backup\Model\Db');
            $backup = $this->_objectManager->create(
                'Magento\Backup\Model\Backup'
            )->setTime(
                time()
            )->setType(
                'db'
            )->setPath(
                $this->_objectManager->get(
                    'Magento\Framework\App\Filesystem'
                )->getPath(
                    \Magento\Framework\App\Filesystem::VAR_DIR
                ) . '/backups'
            );

            $backupDb->createBackup($backup);
            $this->messageManager->addSuccess(__('The database was backed up.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect($failPath, $arguments);
            return;
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('We couldn\'t create a backup right now. Please try again later.')
            );
            $this->_redirect($failPath, $arguments);
            return;
        }
        return $this;
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
