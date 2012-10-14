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

/**
 * Adminhtml newsletter queue controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Newsletter_QueueController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Queue list action
     */
    public function indexAction()
    {
        $this->_title($this->__('Newsletter'))->_title($this->__('Newsletter Queue'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();

        $this->_setActiveMenu('Mage_Newsletter::newsletter_queue');

        $this->_addContent(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Newsletter_Queue', 'queue')
        );

        $this->_addBreadcrumb(Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Queue'), Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Queue'));

        $this->renderLayout();
    }


    /**
     * Drop Newsletter queue template
     */
    public function dropAction ()
    {
        $this->loadLayout('newsletter_queue_preview');
        $this->renderLayout();
    }
    
    /**
     * Preview Newsletter queue template
     */
    public function previewAction()
    {
        $this->loadLayout();
        $data = $this->getRequest()->getParams();
        if (empty($data) || !isset($data['id'])) {
            $this->_forward('noRoute');
            return $this;
        }

        // set default value for selected store
        $data['preview_store_id'] = Mage::app()->getDefaultStoreView()->getId();

        $this->getLayout()->getBlock('preview_form')->setFormData($data);
        $this->renderLayout();
    }

    /**
     * Queue list Ajax action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Newsletter_Queue_Grid')->toHtml()
        );
    }

    public function startAction()
    {
        $queue = Mage::getModel('Mage_Newsletter_Model_Queue')
            ->load($this->getRequest()->getParam('id'));
        if ($queue->getId()) {
            if (!in_array($queue->getQueueStatus(),
                          array(Mage_Newsletter_Model_Queue::STATUS_NEVER,
                                 Mage_Newsletter_Model_Queue::STATUS_PAUSE))) {
                   $this->_redirect('*/*');
                return;
            }

            $queue->setQueueStartAt(Mage::getSingleton('Mage_Core_Model_Date')->gmtDate())
                ->setQueueStatus(Mage_Newsletter_Model_Queue::STATUS_SENDING)
                ->save();
        }

        $this->_redirect('*/*');
    }

    public function pauseAction()
    {
        $queue = Mage::getSingleton('Mage_Newsletter_Model_Queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(),
                      array(Mage_Newsletter_Model_Queue::STATUS_SENDING))) {
               $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(Mage_Newsletter_Model_Queue::STATUS_PAUSE);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function resumeAction()
    {
        $queue = Mage::getSingleton('Mage_Newsletter_Model_Queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(),
                      array(Mage_Newsletter_Model_Queue::STATUS_PAUSE))) {
               $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(Mage_Newsletter_Model_Queue::STATUS_SENDING);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function cancelAction()
    {
        $queue = Mage::getSingleton('Mage_Newsletter_Model_Queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(),
                      array(Mage_Newsletter_Model_Queue::STATUS_SENDING))) {
               $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(Mage_Newsletter_Model_Queue::STATUS_CANCEL);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function sendingAction()
    {
        // Todo: put it somewhere in config!
        $countOfQueue  = 3;
        $countOfSubscritions = 20;

        $collection = Mage::getResourceModel('Mage_Newsletter_Model_Resource_Queue_Collection')
            ->setPageSize($countOfQueue)
            ->setCurPage(1)
            ->addOnlyForSendingFilter()
            ->load();

        $collection->walk('sendPerSubscriber', array($countOfSubscritions));
    }

    public function editAction()
    {
        $this->_title($this->__('Newsletter'))->_title($this->__('Newsletter Queue'));

        Mage::register('current_queue', Mage::getSingleton('Mage_Newsletter_Model_Queue'));

        $id = $this->getRequest()->getParam('id');
        $templateId = $this->getRequest()->getParam('template_id');

        if ($id) {
            $queue = Mage::registry('current_queue')->load($id);
        } elseif ($templateId) {
            $template = Mage::getModel('Mage_Newsletter_Model_Template')->load($templateId);
            $queue = Mage::registry('current_queue')->setTemplateId($template->getId());
        }

        $this->_title($this->__('Edit Queue'));

        $this->loadLayout();

        $this->_setActiveMenu('Mage_Newsletter::newsletter_queue');

        $this->_addBreadcrumb(
            Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Queue'), 
            Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Queue'), 
            $this->getUrl('*/newsletter_queue')
        );
        $this->_addBreadcrumb(Mage::helper('Mage_Newsletter_Helper_Data')->__('Edit Queue'), Mage::helper('Mage_Newsletter_Helper_Data')->__('Edit Queue'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        try {
            /* @var $queue Mage_Newsletter_Model_Queue */
            $queue = Mage::getModel('Mage_Newsletter_Model_Queue');

            $templateId = $this->getRequest()->getParam('template_id');
            if ($templateId) {
                /* @var $template Mage_Newsletter_Model_Template */
                $template = Mage::getModel('Mage_Newsletter_Model_Template')->load($templateId);

                if (!$template->getId() || $template->getIsSystem()) {
                    Mage::throwException($this->__('Wrong newsletter template.'));
                }

                $queue->setTemplateId($template->getId())
                    ->setQueueStatus(Mage_Newsletter_Model_Queue::STATUS_NEVER);
            } else {
                $queue->load($this->getRequest()->getParam('id'));
            }

            if (!in_array($queue->getQueueStatus(),
                   array(Mage_Newsletter_Model_Queue::STATUS_NEVER,
                         Mage_Newsletter_Model_Queue::STATUS_PAUSE))
            ) {
                $this->_redirect('*/*');
                return;
            }

            if ($queue->getQueueStatus() == Mage_Newsletter_Model_Queue::STATUS_NEVER) {
                $queue->setQueueStartAtByString($this->getRequest()->getParam('start_at'));
            }

            $queue->setStores($this->getRequest()->getParam('stores', array()))
                ->setNewsletterSubject($this->getRequest()->getParam('subject'))
                ->setNewsletterSenderName($this->getRequest()->getParam('sender_name'))
                ->setNewsletterSenderEmail($this->getRequest()->getParam('sender_email'))
                ->setNewsletterText($this->getRequest()->getParam('text'))
                ->setNewsletterStyles($this->getRequest()->getParam('styles'));

            if ($queue->getQueueStatus() == Mage_Newsletter_Model_Queue::STATUS_PAUSE
                && $this->getRequest()->getParam('_resume', false)) {
                $queue->setQueueStatus(Mage_Newsletter_Model_Queue::STATUS_SENDING);
            }

            $queue->save();

            $this->_getSession()->addSuccess($this->_getHelper()->__('The newsletter queue has been saved.'));
            $this->_getSession()->setFormData(false);

            $this->_redirect('*/*');
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->_redirect('*/*/edit', array('id' => $id));
            } else {
                $this->_redirectReferer();
            }
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Newsletter::queue');
    }
}
