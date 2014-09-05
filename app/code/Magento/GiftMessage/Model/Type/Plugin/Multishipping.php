<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GiftMessage\Model\Type\Plugin;

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
     * @param array $methods
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetShippingMethods(
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $subject,
        array $methods
    ) {
        $giftMessages = $this->request->getParam('giftmessage');
        $quote = $subject->getQuote();
        $this->message->add($giftMessages, $quote);
    }
}
