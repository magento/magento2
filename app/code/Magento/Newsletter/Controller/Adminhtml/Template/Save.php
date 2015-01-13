<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

class Save extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Save Newsletter Template
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/template'));
        }
        $template = $this->_objectManager->create('Magento\Newsletter\Model\Template');

        $id = (int)$request->getParam('id');
        if ($id) {
            $template->load($id);
        }

        try {
            $template->addData(
                $request->getParams()
            )->setTemplateSubject(
                $request->getParam('subject')
            )->setTemplateCode(
                $request->getParam('code')
            )->setTemplateSenderEmail(
                $request->getParam('sender_email')
            )->setTemplateSenderName(
                $request->getParam('sender_name')
            )->setTemplateText(
                $request->getParam('text')
            )->setTemplateStyles(
                $request->getParam('styles')
            )->setModifiedAt(
                $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate()
            );

            if (!$template->getId()) {
                $template->setTemplateType(\Magento\Newsletter\Model\Template::TYPE_HTML);
            }
            if ($this->getRequest()->getParam('_change_type_flag')) {
                $template->setTemplateType(\Magento\Newsletter\Model\Template::TYPE_TEXT);
                $template->setTemplateStyles('');
            }
            if ($this->getRequest()->getParam('_save_as_flag')) {
                $template->setId(null);
            }

            $template->save();

            $this->messageManager->addSuccess(__('The newsletter template has been saved.'));
            $this->_getSession()->setFormData(false);

            $this->_redirect('*/template');
            return;
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_getSession()->setData('newsletter_template_form_data', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while saving this template.'));
            $this->_getSession()->setData('newsletter_template_form_data', $this->getRequest()->getParams());
        }

        $this->_forward('new');
    }
}
