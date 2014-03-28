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
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Controller\Adminhtml;

class Agreement extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Terms and Conditions'));

        $this->_initAction()->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Checkout\Block\Adminhtml\Agreement')
        );
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Terms and Conditions'));

        $id = $this->getRequest()->getParam('id');
        $agreementModel = $this->_objectManager->create('Magento\Checkout\Model\Agreement');

        if ($id) {
            $agreementModel->load($id);
            if (!$agreementModel->getId()) {
                $this->messageManager->addError(__('This condition no longer exists.'));
                $this->_redirect('checkout/*/');
                return;
            }
        }

        $this->_title->add($agreementModel->getId() ? $agreementModel->getName() : __('New Condition'));

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getAgreementData(true);
        if (!empty($data)) {
            $agreementModel->setData($data);
        }

        $this->_coreRegistry->register('checkout_agreement', $agreementModel);

        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Condition') : __('New Condition'),
            $id ? __('Edit Condition') : __('New Condition')
        )->_addContent(
            $this->_view->getLayout()->createBlock(
                'Magento\Checkout\Block\Adminhtml\Agreement\Edit'
            )->setData(
                'action',
                $this->getUrl('checkout/*/save')
            )
        );
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            $model = $this->_objectManager->get('Magento\Checkout\Model\Agreement');
            $model->setData($postData);

            try {
                $model->save();

                $this->messageManager->addSuccess(__('The condition has been saved.'));
                $this->_redirect('checkout/*/');

                return;
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving this condition.'));
            }

            $this->_objectManager->get('Magento\Backend\Model\Session')->setAgreementData($postData);
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->_objectManager->get('Magento\Checkout\Model\Agreement')->load($id);
        if (!$model->getId()) {
            $this->messageManager->addError(__('This condition no longer exists.'));
            $this->_redirect('checkout/*/');
            return;
        }

        try {
            $model->delete();
            $this->messageManager->addSuccess(__('The condition has been deleted.'));
            $this->_redirect('checkout/*/');
            return;
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong  while deleting this condition.'));
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }

    /**
     * Initialize action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Checkout::sales_checkoutagreement'
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('Checkout Conditions'),
            __('Checkout Terms and Conditions')
        );
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Checkout::checkoutagreement');
    }
}
