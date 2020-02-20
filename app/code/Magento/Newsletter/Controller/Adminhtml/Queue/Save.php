<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Controller\Adminhtml\Queue;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Save extends \Magento\Newsletter\Controller\Adminhtml\Queue implements HttpPostActionInterface
{
    /**
     * Save Newsletter queue
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        try {
            /* @var $queue \Magento\Newsletter\Model\Queue */
            $queue = $this->_objectManager->create(\Magento\Newsletter\Model\Queue::class);

            $templateId = $this->getRequest()->getParam('template_id');
            if ($templateId) {
                /* @var $template \Magento\Newsletter\Model\Template */
                $template = $this->_objectManager->create(\Magento\Newsletter\Model\Template::class)->load($templateId);

                if (!$template->getId() || $template->getIsSystem()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Please correct the newsletter template and try again.')
                    );
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

            $this->messageManager->addSuccess(__('You saved the newsletter queue.'));
            $this->_getSession()->setFormData(false);
            $this->_getSession()->unsPreviewData();

            $this->_redirect('*/*');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
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
