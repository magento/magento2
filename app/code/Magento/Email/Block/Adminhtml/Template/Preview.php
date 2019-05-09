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
 * Email template preview block.
 *
 * @api
 * @since 100.0.2
 */
class Preview extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode
     */
    protected $_maliciousCode;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $_emailFactory;

    /**
     * @var string
     */
    protected $profilerName = 'email_template_proccessing';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param \Magento\Email\Model\TemplateFactory $emailFactory
     * @param array $data
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        /** @var $request \Magento\Framework\App\Request\Http */
        $request = $this->getRequest();

        if (!$request instanceof \Magento\Framework\App\RequestSafetyInterface || !$request->isSafeMethod()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Wrong request.'));
        }

        $storeId = $this->getAnyStoreView()->getId();
        /** @var $template \Magento\Email\Model\Template */
        $template = $this->_emailFactory->create();

        if ($id = (int)$request->getParam('id')) {
            $template->load($id);
        } else {
            $template->setTemplateType($request->getParam('type'));
            $template->setTemplateText($request->getParam('text'));
            $template->setTemplateStyles($request->getParam('styles'));
        }

        \Magento\Framework\Profiler::start($this->profilerName);

        $template->emulateDesign($storeId);
        $templateProcessed = $this->_appState->emulateAreaCode(
            \Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA,
            [$template, 'getProcessedTemplate']
        );
        $template->revertDesign();
        $templateProcessed = $this->_maliciousCode->filter($templateProcessed);

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
