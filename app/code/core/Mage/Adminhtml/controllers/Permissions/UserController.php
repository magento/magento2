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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Adminhtml_Permissions_UserController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/acl')
            ->_addBreadcrumb($this->__('System'), $this->__('System'))
            ->_addBreadcrumb($this->__('Permissions'), $this->__('Permissions'))
            ->_addBreadcrumb($this->__('Users'), $this->__('Users'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Permissions'))
             ->_title($this->__('Users'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Permissions_User'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Permissions'))
             ->_title($this->__('Users'));

        $id = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('Mage_Admin_Model_User');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('This user no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New User'));

        // Restore previously entered form data from session
        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('permissions_user', $model);

        if (isset($id)) {
            $breadcrumb = $this->__('Edit User');
        } else {
            $breadcrumb = $this->__('New User');
        }
        $this->_initAction()
            ->_addBreadcrumb($breadcrumb, $breadcrumb)
            ->_addContent(
                $this->getLayout()
                    ->createBlock('Mage_Adminhtml_Block_Permissions_User_Edit')
                    ->setData('action', $this->getUrl('*/permissions_user/save'))
            )
            ->_addLeft($this->getLayout()->createBlock('Mage_Adminhtml_Block_Permissions_User_Edit_Tabs'));

        $this->_addJs(
            $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Template')
                ->setTemplate('permissions/user_roles_grid_js.phtml')
        );
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('user_id');
            $model = Mage::getModel('Mage_Admin_Model_User')->load($id);
            if (!$model->getId() && $id) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('This user no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $model->setData($data);

            /*
             * Unsetting new password and password confirmation if they are blank
             */
            if ($model->hasNewPassword() && $model->getNewPassword() === '') {
                $model->unsNewPassword();
            }
            if ($model->hasPasswordConfirmation() && $model->getPasswordConfirmation() === '') {
                $model->unsPasswordConfirmation();
            }

            $result = $model->validate();
            if (is_array($result)) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setUserData($data);
                foreach ($result as $message) {
                    Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($message);
                }
                $this->_redirect('*/*/edit', array('_current' => true));
                return $this;
            }

            try {
                $model->save();
                if ( $uRoles = $this->getRequest()->getParam('roles', false) ) {
                    /*parse_str($uRoles, $uRoles);
                    $uRoles = array_keys($uRoles);*/
                    if ( 1 == sizeof($uRoles) ) {
                        $model->setRoleIds($uRoles)
                            ->setRoleUserId($model->getUserId())
                            ->saveRelations();
                    } else if ( sizeof($uRoles) > 1 ) {
                        //@FIXME: stupid fix of previous multi-roles logic.
                        //@TODO:  make proper DB upgrade in the future revisions.
                        $rs = array();
                        $rs[0] = $uRoles[0];
                        $model->setRoleIds( $rs )->setRoleUserId( $model->getUserId() )->saveRelations();
                    }
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess($this->__('The user has been saved.'));
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setUserData(false);
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setUserData($data);
                $this->_redirect('*/*/edit', array('user_id' => $model->getUserId()));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $currentUser = Mage::getSingleton('Mage_Admin_Model_Session')->getUser();

        if ($id = $this->getRequest()->getParam('user_id')) {
            if ( $currentUser->getId() == $id ) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('You cannot delete your own account.'));
                $this->_redirect('*/*/edit', array('user_id' => $id));
                return;
            }
            try {
                $model = Mage::getModel('Mage_Admin_Model_User');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess($this->__('The user has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('user_id' => $this->getRequest()->getParam('user_id')));
                return;
            }
        }
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('Unable to find a user to delete.'));
        $this->_redirect('*/*/');
    }

    public function rolesGridAction()
    {
        $id = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('Mage_Admin_Model_User');

        if ($id) {
            $model->load($id);
        }

        Mage::register('permissions_user', $model);
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Roles')
                ->toHtml()
        );
    }

    public function roleGridAction()
    {
        $this->getResponse()
            ->setBody($this->getLayout()
            ->createBlock('Mage_Adminhtml_Block_Permissions_User_Grid')
            ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('system/acl/users');
    }

}
