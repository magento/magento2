<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

class DefaultTemplate extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Email\Model\Template\Config $emailConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Email\Model\Template\Config $emailConfig
    ) {
        $this->emailConfig = $emailConfig;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Set template data to retrieve it in template info form
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $template = $this->_initTemplate('id');
        $templateId = $this->getRequest()->getParam('code');
        try {
            $parts = $this->emailConfig->parseTemplateIdParts($templateId);
            $templateId = $parts['templateId'];
            $theme = $parts['theme'];

            if ($theme) {
                $template->setForcedTheme($templateId, $theme);
            }
            $template->setForcedArea($templateId);

            $template->loadDefault($templateId);
            $template->setData('orig_template_code', $templateId);
            $template->setData('template_variables', \Zend_Json::encode($template->getVariablesOptionArray(true)));

            $templateBlock = $this->_view->getLayout()->createBlock(
                \Magento\Email\Block\Adminhtml\Template\Edit::class
            );
            $template->setData('orig_template_currently_used_for', $templateBlock->getCurrentlyUsedForPaths(false));

            $this->getResponse()->representJson(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($template->getData())
            );
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }
}
