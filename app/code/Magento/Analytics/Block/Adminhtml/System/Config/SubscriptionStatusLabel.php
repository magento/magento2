<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class SubscriptionStatusLabel.
 */
class SubscriptionStatusLabel extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatusProvider;

    /**
     * SubscriptionStatusLabel constructor.
     *
     * @param Context $context
     * @param SubscriptionStatusProvider $labelStatusProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        SubscriptionStatusProvider $labelStatusProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->subscriptionStatusProvider = $labelStatusProvider;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        $element->setData(
            'value',
            $this->prepareLabelValue()
        );
        return parent::render($element);
    }

    /**
     * @return string
     */
    private function prepareLabelValue()
    {
        return __('Subscription status').': '.__($this->subscriptionStatusProvider->getStatus());
    }
}
