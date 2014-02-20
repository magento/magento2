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
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extension controller
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Controller\Adminhtml\Extension;

class Custom extends \Magento\Backend\App\Action
{
    /**
     * Redirect to edit Extension Package action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Package Extensions'));

        $this->_forward('edit');
    }

    /**
     * Edit Extension Package Form
     *
     * @return void
     */
    public function editAction()
    {
        $this ->_title->add(__('Extension'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Connect::system_extensions_custom');
        $this->_view->renderLayout();
    }

    /**
     * Reset Extension Package form data
     *
     * @return void
     */
    public function resetAction()
    {
        $this->_objectManager->get('Magento\Connect\Model\Session')->unsCustomExtensionPackageFormData();
        $this->_redirect('adminhtml/*/edit');
    }

    /**
     * Load Local Extension Package
     *
     * @return void
     */
    public function loadAction()
    {
        $packageName = base64_decode(strtr($this->getRequest()->getParam('id'), '-_,', '+/='));
        if ($packageName) {
            $session = $this->_objectManager->get('Magento\Connect\Model\Session');
            try {
                $data = $this->_objectManager->get('Magento\Connect\Helper\Data')->loadLocalPackage($packageName);
                if (!$data) {
                    throw new \Magento\Core\Exception(__('Something went wrong loading the package data.'));
                }
                $data = array_merge($data, array('file_name' => $packageName));
                $session->setCustomExtensionPackageFormData($data);
                $this->messageManager->addSuccess(
                    __('The package %1 data has been loaded.', $packageName)
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/*/edit');
    }

    /**
     * Save Extension Package
     *
     * @return void
     */
    public function saveAction()
    {
        $session = $this->_objectManager->get('Magento\Connect\Model\Session');
        $p = $this->getRequest()->getPost();

        if (!empty($p['_create'])) {
            $create = true;
            unset($p['_create']);
        }

        if ($p['file_name'] == '') {
            $p['file_name'] = $p['name'];
        }

        $session->setCustomExtensionPackageFormData($p);
        try {
            $ext = $this->_objectManager->create('Magento\Connect\Model\Extension');
            /** @var $ext \Magento\Connect\Model\Extension */
            $ext->setData($p);
            if ($ext->savePackage()) {
                $this->messageManager->addSuccess(__('The package data has been saved.'));
            } else {
                $this->messageManager->addError(__('Something went wrong saving the package data.'));
                $this->_redirect('adminhtml/*/edit');
            }
            if (empty($create)) {
                $this->_redirect('adminhtml/*/edit');
            } else {
                $this->_forward('create');
            }
        } catch (\Magento\Core\Exception $e){
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('adminhtml/*');
        } catch (\Exception $e){
            $this->messageManager->addException($e, __('Something went wrong saving the package.'));
            $this->_redirect('adminhtml/*');
        }
    }

    /**
     * Create new Extension Package
     *
     * @return void
     */
    public function createAction()
    {
        $session = $this->_objectManager->get('Magento\Connect\Model\Session');
        try {
            $post = $this->getRequest()->getPost();
            $session->setCustomExtensionPackageFormData($post);
            $ext = $this->_objectManager->create('Magento\Connect\Model\Extension');
            $ext->setData($post);
            $packageVersion = $this->getRequest()->getPost('version_ids');
            if (is_array($packageVersion)) {
                if (in_array(\Magento\Connect\Package::PACKAGE_VERSION_2X, $packageVersion)) {
                    $ext->createPackage();
                }
                if (in_array(\Magento\Connect\Package::PACKAGE_VERSION_1X, $packageVersion)) {
                    $ext->createPackageV1x();
                }
            }
            $this->_redirect('adminhtml/*');
        } catch(\Magento\Core\Exception $e){
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('adminhtml/*');
        } catch(\Exception $e){
            $this->messageManager->addException($e, __('Something went wrong creating the package.'));
            $this->_redirect('adminhtml/*');
        }
    }

    /**
     * Load Grid with Local Packages
     *
     * @return void
     */
    public function loadtabAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Grid for loading packages
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Check is allowed access to actions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Adminhtml::custom');
    }
}
