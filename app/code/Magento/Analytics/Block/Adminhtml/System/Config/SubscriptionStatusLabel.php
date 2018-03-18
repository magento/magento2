<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Backend\Block\Template\Context;

/**
<<<<<<< HEAD
=======
 * Class SubscriptionStatusLabel.
 *
>>>>>>> upstream/2.2-develop
 * Provides labels for subscription status
 * Status can be reviewed in System Configuration
 */
class SubscriptionStatusLabel extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatusProvider;

    /**
<<<<<<< HEAD
=======
     * SubscriptionStatusLabel constructor.
     *
>>>>>>> upstream/2.2-develop
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
     * Add Subscription status to comment
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
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
     */
    private function prepareLabelValue()
    {
<<<<<<< HEAD
        return __('Subscription status') . ': ' . $this->subscriptionStatusProvider->getStatus();
=======
        return __('Subscription status') . ': ' . __($this->subscriptionStatusProvider->getStatus());
>>>>>>> upstream/2.2-develop
    }
}
