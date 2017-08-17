<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\Type\Plugin;

/**
 * Class \Magento\GiftMessage\Model\Type\Plugin\Onepage
 *
 */
class Onepage
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
     * @param \Magento\Checkout\Model\Type\Onepage $subject
     * @param array $result
     * @return $this
     */
    public function afterSaveShippingMethod(
        \Magento\Checkout\Model\Type\Onepage $subject,
        array $result
    ) {
        if (!$result) {
            $giftMessages = $this->request->getParam('giftmessage');
            $quote = $subject->getQuote();
            $this->message->add($giftMessages, $quote);
        }
        return $result;
    }
}
