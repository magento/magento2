<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Block\Adminhtml\Template;

use Magento\Email\Model\Template\Validator as TemplateValidator;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\App\ObjectManager;

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
     * @var TemplateValidator
     */
    private $templateValidator;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param \Magento\Email\Model\TemplateFactory $emailFactory
     * @param array $data
     * @param TemplateValidator $templateValidator
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Magento\Email\Model\TemplateFactory $emailFactory,
        array $data = [],
        ?TemplateValidator $templateValidator = null
    ) {
        $this->_maliciousCode = $maliciousCode;
        $this->_emailFactory = $emailFactory;
        $this->templateValidator = $templateValidator ?:
            ObjectManager::getInstance()->get(TemplateValidator::class);
        parent::__construct($context, $data);
    }

    /**
     * Prepare html output
     *
     * @return string
     * @throws \Exception
     */
    protected function _toHtml()
    {
        $request = $this->getRequest();

        $storeId = $this->getAnyStoreView()->getId();
        /** @var $template \Magento\Email\Model\Template */
        $template = $this->_emailFactory->create();

        if ($id = (int)$request->getParam('id')) {
            $template->load($id);
        } else {
            $rawText = $request->getParam('text');
            $rawText = is_string($rawText) ? $rawText : '';
            $rawStyles = $request->getParam('styles');
            $rawStyles = is_string($rawStyles) ? $rawStyles : '';
            try {
                $this->templateValidator->validate($rawText);
                if ($rawStyles !== '') {
                    $this->templateValidator->validate($rawStyles);
                }
            } catch (ValidationException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Template content validation failed. Please review template syntax.')
                );
            }
            $template->setTemplateType($request->getParam('type'));
            $template->setTemplateText($this->_maliciousCode->filter($rawText));
            $template->setTemplateStyles($rawStyles);
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
            $templateProcessed = "<pre>" . $this->escapeHtml($templateProcessed) . "</pre>";
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
