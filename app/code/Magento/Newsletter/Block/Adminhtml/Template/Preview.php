<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Block\Adminhtml\Template;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\Input\MaliciousCode;
use Magento\Framework\Profiler;
use Magento\Newsletter\Model\TemplateFactory;
use Magento\Newsletter\Model\Template;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Newsletter template preview block
 *
 * @api
 * @since 100.0.2
 */
class Preview extends Widget
{
    /**
     * Name for profiler
     *
     * @var string
     */
    protected $profilerName = "newsletter_template_proccessing";

    /**
     * @var TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var MaliciousCode
     */
    private $maliciousCode;

    /**
     * @param Context $context
     * @param TemplateFactory $templateFactory
     * @param SubscriberFactory $subscriberFactory
     * @param array $data
     * @param ?MaliciousCode $maliciousCode
     */
    public function __construct(
        Context $context,
        TemplateFactory $templateFactory,
        SubscriberFactory $subscriberFactory,
        array $data = [],
        ?MaliciousCode $maliciousCode = null
    ) {
        $this->_templateFactory = $templateFactory;
        $this->_subscriberFactory = $subscriberFactory;
        parent::__construct($context, $data);
        $this->maliciousCode = $maliciousCode ?:
            ObjectManager::getInstance()->get(MaliciousCode::class);
    }

    /**
     * Get html code
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var $template \Magento\Newsletter\Model\Template */
        $template = $this->_templateFactory->create();

        if ($id = (int)$this->getRequest()->getParam('id')) {
            $this->loadTemplate($template, $id);
        } else {
            $previewData = $this->getPreviewData();
            $template->setTemplateType($previewData['type']);
            $template->setTemplateText($previewData['text']);
            $template->setTemplateStyles($previewData['styles']);
        }

        Profiler::start($this->profilerName);
        $vars = [];

        $vars['subscriber'] = $this->_subscriberFactory->create();
        if ($this->getRequest()->getParam('subscriber')) {
            $vars['subscriber']->load($this->getRequest()->getParam('subscriber'));
        }
        $vars['subscriber_data']['unsubscription_link'] = $vars['subscriber'] ?
            $vars['subscriber']->getUnsubscriptionLink() :
            null;
        $template->emulateDesign($this->getStoreId());
        $templateProcessed = $this->_appState->emulateAreaCode(
            Template::DEFAULT_DESIGN_AREA,
            [$template, 'getProcessedTemplate'],
            [$vars]
        );
        $template->revertDesign();
        $templateProcessed = $this->maliciousCode->filter($templateProcessed);
        if ($template->isPlain()) {
            $templateProcessed = "<pre>" . $this->escapeHtml($templateProcessed) . "</pre>";
        }

        Profiler::stop($this->profilerName);

        return $templateProcessed;
    }

    /**
     * Return template preview data
     *
     * @return array
     */
    private function getPreviewData()
    {
        $previewData = [];
        $previewParams = ['type', 'text', 'styles'];

        $sessionData = [];
        if ($this->_backendSession->hasPreviewData()) {
            $sessionData = $this->_backendSession->getPreviewData();
        }

        foreach ($previewParams as $param) {
            if (isset($sessionData[$param])) {
                $previewData[$param] = $sessionData[$param];
            } else {
                $previewData[$param] = $this->getRequest()->getParam($param);
            }
        }

        return $previewData;
    }

    /**
     * Get Store Id from request or default
     *
     * @return int|null
     */
    protected function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        if ($storeId) {
            return $storeId;
        }

        $defaultStore = $this->_storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $this->_storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }

        return $defaultStore ? $defaultStore->getId() : null;
    }

    /**
     * Return template
     *
     * @param Template $template
     * @param string $id
     * @return $this
     */
    protected function loadTemplate(Template $template, $id)
    {
        $template->load($id);
        return $this;
    }
}
