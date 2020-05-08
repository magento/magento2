<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Email\Controller\Adminhtml\Email\Template;
use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Save Controller
 */
class Save extends Template implements HttpPostActionInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TemplateResource
     */
    private $templateResource;

    /**
     * @var Session
     */
    private $backendSession;

    /**
     * Save constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DateTime|null $dateTime
     * @param TemplateResource|null $templateResource
     * @param Session|null $backendSession
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DateTime $dateTime = null,
        TemplateResource $templateResource = null,
        Session $backendSession = null
    ) {
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()->get(DateTime::class);
        $this->templateResource = $templateResource ?: ObjectManager::getInstance()->get(TemplateResource::class);
        $this->backendSession = $backendSession ?: ObjectManager::getInstance()->get(Session::class);
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Save transactional email action
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();
        $templateId = $this->getRequest()->getParam('id');

        $template = $this->_initTemplate('id');
        if (!$template->getId() && $templateId) {
            $this->messageManager->addErrorMessage(__('This email template no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        try {
            $template->setTemplateSubject(
                $request->getParam('template_subject')
            )->setTemplateCode(
                $request->getParam('template_code')
            )->setTemplateText(
                $request->getParam('template_text')
            )->setTemplateStyles(
                $request->getParam('template_styles')
            )->setModifiedAt(
                $this->dateTime->gmtDate()
            )->setOrigTemplateCode(
                $request->getParam('orig_template_code')
            )->setOrigTemplateVariables(
                $request->getParam('orig_template_variables')
            );

            if (!$template->getId()) {
                $template->setTemplateType(TemplateTypesInterface::TYPE_HTML);
            }

            if ($request->getParam('_change_type_flag')) {
                $template->setTemplateType(TemplateTypesInterface::TYPE_TEXT);
                $template->setTemplateStyles('');
            }

            $this->templateResource->save($template);

            $this->backendSession->setFormData(false);
            $this->messageManager->addSuccessMessage(__('You saved the email template.'));
            $this->_redirect('adminhtml/*');
        } catch (Exception $e) {
            $this->backendSession->setData('email_template_form_data', $request->getParams());
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_forward('new');
        }
    }
}
