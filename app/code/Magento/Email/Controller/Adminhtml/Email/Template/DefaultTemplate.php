<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

/**
 * Class \Magento\Email\Controller\Adminhtml\Email\Template\DefaultTemplate
 *
 * @since 2.0.0
 */
class DefaultTemplate extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * @var \Magento\Email\Model\Template\Config
     * @since 2.0.0
     */
    private $emailConfig;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->emailConfig = $emailConfig;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Set template data to retrieve it in template info form
     *
     * @return void
     * @throws \RuntimeException
     * @since 2.0.0
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
            $template->setData(
                'template_variables',
                $this->serializer->serialize($template->getVariablesOptionArray(true))
            );

            $templateBlock = $this->_view->getLayout()->createBlock(
                \Magento\Email\Block\Adminhtml\Template\Edit::class
            );
            $template->setData('orig_template_currently_used_for', $templateBlock->getCurrentlyUsedForPaths(false));

            $this->getResponse()->representJson(
                $this->serializer->serialize($template->getData())
            );
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }
}
