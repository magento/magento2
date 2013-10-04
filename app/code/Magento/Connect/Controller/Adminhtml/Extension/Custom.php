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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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

class Custom extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Redirect to edit Extension Package action
     *
     */
    public function indexAction()
    {
        $this->_title(__('Package Extensions'));

        $this->_forward('edit');
    }

    /**
     * Edit Extension Package Form
     *
     */
    public function editAction()
    {
        $this ->_title(__('Extension'));

        $this->loadLayout();
        $this->_setActiveMenu('Magento_Connect::system_extensions_custom');
        $this->renderLayout();
    }

    /**
     * Reset Extension Package form data
     *
     */
    public function resetAction()
    {
        $this->_objectManager->get('Magento\Connect\Model\Session')->unsCustomExtensionPackageFormData();
        $this->_redirect('*/*/edit');
    }

    /**
     * Load Local Extension Package
     *
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
                $session->addSuccess(
                    __('The package %1 data has been loaded.', $packageName)
                );
            } catch (\Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/edit');
    }

    /**
     * Save Extension Package
     *
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
                $session->addSuccess(__('The package data has been saved.'));
            } else {
                $session->addError(__('Something went wrong saving the package data.'));
                $this->_redirect('*/*/edit');
            }
            if (empty($create)) {
                $this->_redirect('*/*/edit');
            } else {
                $this->_forward('create');
            }
        } catch (\Magento\Core\Exception $e){
            $session->addError($e->getMessage());
            $this->_redirect('*/*');
        } catch (\Exception $e){
            $session->addException($e, __('Something went wrong saving the package.'));
            $this->_redirect('*/*');
        }
    }

    /**
     * Create new Extension Package
     *
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
            $this->_redirect('*/*');
        } catch(\Magento\Core\Exception $e){
            $session->addError($e->getMessage());
            $this->_redirect('*/*');
        } catch(\Exception $e){
            $session->addException($e, __('Something went wrong creating the package.'));
            $this->_redirect('*/*');
        }
    }

    /**
     * Load Grid with Local Packages
     *
     */
    public function loadtabAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Grid for loading packages
     *
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
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
