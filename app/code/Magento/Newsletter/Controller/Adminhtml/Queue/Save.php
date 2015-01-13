<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

class Save extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Save Newsletter queue
     *
     * @throws \Magento\Framework\Model\Exception
     * @return void
     */
    public function execute()
    {
        try {
            /* @var $queue \Magento\Newsletter\Model\Queue */
            $queue = $this->_objectManager->create('Magento\Newsletter\Model\Queue');

            $templateId = $this->getRequest()->getParam('template_id');
            if ($templateId) {
                /* @var $template \Magento\Newsletter\Model\Template */
                $template = $this->_objectManager->create('Magento\Newsletter\Model\Template')->load($templateId);

                if (!$template->getId() || $template->getIsSystem()) {
                    throw new \Magento\Framework\Model\Exception(__('Please correct the newsletter template and try again.'));
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
                [\Magento\Newsletter\Model\Queue::STATUS_NEVER, \Magento\Newsletter\Model\Queue::STATUS_PAUSE]
            )
            ) {
                $this->_redirect('*/*');
                return;
            }

            if ($queue->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_NEVER) {
                $queue->setQueueStartAtByString($this->getRequest()->getParam('start_at'));
            }

            $queue->setStores(
                $this->getRequest()->getParam('stores', [])
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

            if ($queue->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_PAUSE
                && $this->getRequest()->getParam(
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
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->_redirect('*/*/edit', ['id' => $id]);
            } else {
                $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            }
        }
    }
}
