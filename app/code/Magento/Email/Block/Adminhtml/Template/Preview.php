<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml system template preview block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Email\Block\Adminhtml\Template;

/**
 * @api
 * @since 2.0.0
 */
class Preview extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode
     * @since 2.0.0
     */
    protected $_maliciousCode;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     * @since 2.0.0
     */
    protected $_emailFactory;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $profilerName = 'email_template_proccessing';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param \Magento\Email\Model\TemplateFactory $emailFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Magento\Email\Model\TemplateFactory $emailFactory,
        array $data = []
    ) {
        $this->_maliciousCode = $maliciousCode;
        $this->_emailFactory = $emailFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare html output
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $storeId = $this->getAnyStoreView()->getId();
        /** @var $template \Magento\Email\Model\Template */
        $template = $this->_emailFactory->create();

        if ($id = (int)$this->getRequest()->getParam('id')) {
            $template->load($id);
        } else {
            $template->setTemplateType($this->getRequest()->getParam('type'));
            $template->setTemplateText($this->getRequest()->getParam('text'));
            $template->setTemplateStyles($this->getRequest()->getParam('styles'));
        }

        $template->setTemplateText($this->_maliciousCode->filter($template->getTemplateText()));

        \Magento\Framework\Profiler::start($this->profilerName);

        $template->emulateDesign($storeId);
        $templateProcessed = $this->_appState->emulateAreaCode(
            \Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA,
            [$template, 'getProcessedTemplate']
        );
        $template->revertDesign();

        if ($template->isPlain()) {
            $templateProcessed = "<pre>" . htmlspecialchars($templateProcessed) . "</pre>";
        }

        \Magento\Framework\Profiler::stop($this->profilerName);

        return $templateProcessed;
    }

    /**
     * Get either default or any store view
     *
     * @return \Magento\Store\Model\Store|null
     * @since 2.0.0
     */
    protected function getAnyStoreView()
    {
        $store = $this->_storeManager->getDefaultStoreView();
        if ($store) {
            return $store;
        }
        foreach ($this->_storeManager->getStores() as $store) {
            return $store;
        }
        return null;
    }
}
