<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter template preview block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Queue;

class Preview extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Newsletter\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\Newsletter\Model\QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Newsletter\Model\TemplateFactory $templateFactory
     * @param \Magento\Newsletter\Model\QueueFactory $queueFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory,
        \Magento\Newsletter\Model\QueueFactory $queueFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        array $data = []
    ) {
        $this->_templateFactory = $templateFactory;
        $this->_queueFactory = $queueFactory;
        $this->_subscriberFactory = $subscriberFactory;
        parent::__construct($context, $data);
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
            $queue = $this->_queueFactory->create()->load($id);
            $template->setTemplateType($queue->getNewsletterType());
            $template->setTemplateText($queue->getNewsletterText());
            $template->setTemplateStyles($queue->getNewsletterStyles());
        } else {
            $template->setTemplateType($this->getRequest()->getParam('type'));
            $template->setTemplateText($this->getRequest()->getParam('text'));
            $template->setTemplateStyles($this->getRequest()->getParam('styles'));
        }

        $storeId = (int)$this->getRequest()->getParam('store_id');
        if (!$storeId) {
            $defaultStore = $this->_storeManager->getDefaultStoreView();
            if (!$defaultStore) {
                $allStores = $this->_storeManager->getStores();
                if (isset($allStores[0])) {
                    $defaultStore = $allStores[0];
                }
            }
            $storeId = $defaultStore ? $defaultStore->getId() : null;
        }

        \Magento\Framework\Profiler::start("newsletter_queue_proccessing");
        $vars = [];

        $vars['subscriber'] = $this->_subscriberFactory->create();

        $template->emulateDesign($storeId);
        $templateProcessed = $this->_appState->emulateAreaCode(
            \Magento\Newsletter\Model\Template::DEFAULT_DESIGN_AREA,
            [$template, 'getProcessedTemplate'],
            [$vars, true]
        );
        $template->revertDesign();

        if ($template->isPlain()) {
            $templateProcessed = "<pre>" . htmlspecialchars($templateProcessed) . "</pre>";
        }

        \Magento\Framework\Profiler::stop("newsletter_queue_proccessing");

        return $templateProcessed;
    }
}
