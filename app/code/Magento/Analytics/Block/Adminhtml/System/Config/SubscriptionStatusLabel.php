<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Backend\Block\Template\Context;

/**
 * Class SubscriptionStatusLabel.
 *
 * Provides labels for subscription status
 * Status can be reviewed in System Configuration
 * @since 2.2.0
 */
class SubscriptionStatusLabel extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var SubscriptionStatusProvider
     * @since 2.2.0
     */
    private $subscriptionStatusProvider;

    /**
     * SubscriptionStatusLabel constructor.
     *
     * @param Context $context
     * @param SubscriptionStatusProvider $labelStatusProvider
     * @param array $data
     * @since 2.2.0
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
     * Add Subscription status to comment
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @since 2.2.0
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setData(
            'comment',
            $this->prepareLabelValue()
        );
        return parent::render($element);
    }

    /**
     * Prepare label for subscription status
     *
     * @return string
     * @since 2.2.0
     */
    private function prepareLabelValue()
    {
        return __('Subscription status') . ': ' . __($this->subscriptionStatusProvider->getStatus());
    }
}
