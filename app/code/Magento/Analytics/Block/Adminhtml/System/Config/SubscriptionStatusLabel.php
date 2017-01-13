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
     * @param SubscriptionStatusProvider $labelStatusProvider
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        SubscriptionStatusProvider $labelStatusProvider,
        Context $context,
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
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return __('Subscription status'). ': ' . __($this->subscriptionStatusProvider->getStatus());
    }
}
