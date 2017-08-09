<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\Type\Plugin;

/**
 * Class \Magento\GiftMessage\Model\Type\Plugin\Multishipping
 *
 */
class Multishipping
{
    /**
     * @var \Magento\GiftMessage\Model\GiftMessageManager
     */
    protected $message;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\GiftMessage\Model\GiftMessageManager $message
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\GiftMessage\Model\GiftMessageManager $message,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->message = $message;
        $this->request = $request;
    }

    /**
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $subject
     * @param array|null $methods
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetShippingMethods(
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $subject,
        $methods
    ) {
        $giftMessages = $this->request->getParam('giftmessage');
        $quote = $subject->getQuote();
        $this->message->add($giftMessages, $quote);
    }
}
