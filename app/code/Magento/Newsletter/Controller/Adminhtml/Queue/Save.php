<?php
/**
 *
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
                $this->_redirect('*/*/edit', array('id' => $id));
            } else {
                $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            }
        }
    }
}
