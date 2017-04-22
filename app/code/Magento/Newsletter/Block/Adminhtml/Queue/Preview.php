<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Block\Adminhtml\Queue;

/**
 * Newsletter template preview block
 *
 * @api
 */
class Preview extends \Magento\Newsletter\Block\Adminhtml\Template\Preview
{
    /**
     * {@inheritdoc}
     */
    protected $profilerName = "newsletter_queue_proccessing";

    /**
     * @var \Magento\Newsletter\Model\QueueFactory
     */
    protected $_queueFactory;

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
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Newsletter\Model\QueueFactory $queueFactory,
        array $data = []
    ) {
        $this->_queueFactory = $queueFactory;
        parent::__construct($context, $templateFactory, $subscriberFactory, $data);
    }

    /**
     * @param \Magento\Newsletter\Model\Template $template
     * @param string $id
     * @return $this
     */
    protected function loadTemplate(\Magento\Newsletter\Model\Template $template, $id)
    {
        /** @var \Magento\Newsletter\Model\Queue $queue */
        $queue = $this->_queueFactory->create()->load($id);
        $template->setTemplateType($queue->getNewsletterType());
        $template->setTemplateText($queue->getNewsletterText());
        $template->setTemplateStyles($queue->getNewsletterStyles());
        return $this;
    }
}
