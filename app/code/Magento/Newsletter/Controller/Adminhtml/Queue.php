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
 * @package     Magento_Newsletter
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter queue controller
 *
 * @category   Magento
 * @package    Magento_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Controller\Adminhtml;

class Queue extends \Magento\Backend\App\Action
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
     * Queue list action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Newsletter Queue'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_queue');

        $this->_addBreadcrumb(__('Newsletter Queue'), __('Newsletter Queue'));

        $this->_view->renderLayout();
    }

    /**
     * Drop Newsletter queue template
     *
     * @return void
     */
    public function dropAction()
    {
        $this->_view->loadLayout('newsletter_queue_preview_popup');
        $this->_view->renderLayout();
    }

    /**
     * Preview Newsletter queue template
     *
     * @return void
     */
    public function previewAction()
    {
        $this->_view->loadLayout();
        $data = $this->getRequest()->getParams();
        if (empty($data) || !isset($data['id'])) {
            $this->_forward('noroute');
            return;
        }

        // set default value for selected store
        $data['preview_store_id'] = $this->_objectManager->get(
            'Magento\Core\Model\StoreManager'
        )->getDefaultStoreView()->getId();

        $this->_view->getLayout()->getBlock('preview_form')->setFormData($data);
        $this->_view->renderLayout();
    }

    /**
     * Queue list Ajax action
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Start Newsletter queue
     *
     * @return void
     */
    public function startAction()
    {
        $queue = $this->_objectManager->create(
            'Magento\Newsletter\Model\Queue'
        )->load(
            $this->getRequest()->getParam('id')
        );
        if ($queue->getId()) {
            if (!in_array(
                $queue->getQueueStatus(),
                array(\Magento\Newsletter\Model\Queue::STATUS_NEVER, \Magento\Newsletter\Model\Queue::STATUS_PAUSE)
            )
            ) {
                $this->_redirect('*/*');
                return;
            }

            $queue->setQueueStartAt(
                $this->_objectManager->get('Magento\Stdlib\DateTime\DateTime')->gmtDate()
            )->setQueueStatus(
                \Magento\Newsletter\Model\Queue::STATUS_SENDING
            )->save();
        }

        $this->_redirect('*/*');
    }

    /**
     * Pause Newsletter queue
     *
     * @return void
     */
    public function pauseAction()
    {
        $queue = $this->_objectManager->get(
            'Magento\Newsletter\Model\Queue'
        )->load(
            $this->getRequest()->getParam('id')
        );

        if (!in_array($queue->getQueueStatus(), array(\Magento\Newsletter\Model\Queue::STATUS_SENDING))) {
            $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_PAUSE);
        $queue->save();

        $this->_redirect('*/*');
    }

    /**
     * Resume Newsletter queue
     *
     * @return void
     */
    public function resumeAction()
    {
        $queue = $this->_objectManager->get(
            'Magento\Newsletter\Model\Queue'
        )->load(
            $this->getRequest()->getParam('id')
        );

        if (!in_array($queue->getQueueStatus(), array(\Magento\Newsletter\Model\Queue::STATUS_PAUSE))) {
            $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_SENDING);
        $queue->save();

        $this->_redirect('*/*');
    }

    /**
     * Cancel Newsletter queue
     *
     * @return void
     */
    public function cancelAction()
    {
        $queue = $this->_objectManager->get(
            'Magento\Newsletter\Model\Queue'
        )->load(
            $this->getRequest()->getParam('id')
        );

        if (!in_array($queue->getQueueStatus(), array(\Magento\Newsletter\Model\Queue::STATUS_SENDING))) {
            $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_CANCEL);
        $queue->save();

        $this->_redirect('*/*');
    }

    /**
     * Send Newsletter queue
     *
     * @return void
     */
    public function sendingAction()
    {
        // Todo: put it somewhere in config!
        $countOfQueue = 3;
        $countOfSubscritions = 20;

        $collection = $this->_objectManager->create(
            'Magento\Newsletter\Model\Resource\Queue\Collection'
        )->setPageSize(
            $countOfQueue
        )->setCurPage(
            1
        )->addOnlyForSendingFilter()->load();

        $collection->walk('sendPerSubscriber', array($countOfSubscritions));
    }

    /**
     * Edit Newsletter queue
     *
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Newsletter Queue'));

        $this->_coreRegistry->register('current_queue', $this->_objectManager->get('Magento\Newsletter\Model\Queue'));

        $id = $this->getRequest()->getParam('id');
        $templateId = $this->getRequest()->getParam('template_id');

        if ($id) {
            $queue = $this->_coreRegistry->registry('current_queue')->load($id);
        } elseif ($templateId) {
            $template = $this->_objectManager->create('Magento\Newsletter\Model\Template')->load($templateId);
            $queue = $this->_coreRegistry->registry('current_queue')->setTemplateId($template->getId());
        }

        $this->_title->add(__('Edit Queue'));

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_queue');

        $this->_addBreadcrumb(__('Newsletter Queue'), __('Newsletter Queue'), $this->getUrl('*/*'));
        $this->_addBreadcrumb(__('Edit Queue'), __('Edit Queue'));

        $this->_view->renderLayout();
    }

    /**
     * Save Newsletter queue
     *
     * @throws \Magento\Model\Exception
     * @return void
     */
    public function saveAction()
    {
        try {
            /* @var $queue \Magento\Newsletter\Model\Queue */
            $queue = $this->_objectManager->create('Magento\Newsletter\Model\Queue');

            $templateId = $this->getRequest()->getParam('template_id');
            if ($templateId) {
                /* @var $template \Magento\Newsletter\Model\Template */
                $template = $this->_objectManager->create('Magento\Newsletter\Model\Template')->load($templateId);

                if (!$template->getId() || $template->getIsSystem()) {
                    throw new \Magento\Model\Exception(__('Please correct the newsletter template and try again.'));
                }

                $queue->setTemplateId(
                    $template->getId()
                )->setQueueStatus(
                    \Magento\Newsletter\Model\Queue::STATUS_NEVER
                );
            } else {
                $queue->load($this->getRequest()->getParam('id'));
            }

            if (!in_array(
                $queue->getQueueStatus(),
                array(\Magento\Newsletter\Model\Queue::STATUS_NEVER, \Magento\Newsletter\Model\Queue::STATUS_PAUSE)
            )
            ) {
                $this->_redirect('*/*');
                return;
            }

            if ($queue->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_NEVER) {
                $queue->setQueueStartAtByString($this->getRequest()->getParam('start_at'));
            }

            $queue->setStores(
                $this->getRequest()->getParam('stores', array())
            )->setNewsletterSubject(
                $this->getRequest()->getParam('subject')
            )->setNewsletterSenderName(
                $this->getRequest()->getParam('sender_name')
            )->setNewsletterSenderEmail(
                $this->getRequest()->getParam('sender_email')
            )->setNewsletterText(
                $this->getRequest()->getParam('text')
            )->setNewsletterStyles(
                $this->getRequest()->getParam('styles')
            );

            if ($queue->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_PAUSE &&
                $this->getRequest()->getParam(
                    '_resume',
                    false
                )
            ) {
                $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_SENDING);
            }

            $queue->save();

            $this->messageManager->addSuccess(__('The newsletter queue has been saved.'));
            $this->_getSession()->setFormData(false);

            $this->_redirect('*/*');
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->_redirect('*/*/edit', array('id' => $id));
            } else {
                $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            }
        }
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Newsletter::queue');
    }
}
