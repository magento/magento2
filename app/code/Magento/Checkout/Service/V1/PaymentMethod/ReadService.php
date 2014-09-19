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

namespace Magento\Checkout\Service\V1\PaymentMethod;

use \Magento\Sales\Model\QuoteRepository;
use \Magento\Framework\StoreManagerInterface;
use Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Converter as QuoteMethodConverter;
use Magento\Checkout\Service\V1\Data\PaymentMethod\Converter as PaymentMethodConverter;
use \Magento\Payment\Model\MethodList;

class ReadService implements ReadServiceInterface
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var QuoteMethodConverter
     */
    protected $quoteMethodConverter;

    /**
     * @var PaymentMethodConverter
     */
    protected $paymentMethodConverter;

    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * @param QuoteRepository $quoteRepository
     * @param QuoteMethodConverter $quoteMethodConverter
     * @param PaymentMethodConverter $paymentMethodConverter
     * @param MethodList $methodList
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        QuoteMethodConverter $quoteMethodConverter,
        PaymentMethodConverter $paymentMethodConverter,
        MethodList $methodList
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteMethodConverter = $quoteMethodConverter;
        $this->paymentMethodConverter = $paymentMethodConverter;
        $this->methodList = $methodList;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayment($cartId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        $payment = $quote->getPayment();
        if (!$payment->getId()) {
            return null;
        }
        return $this->quoteMethodConverter->toDataObject($payment);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $output = [];
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        foreach ($this->methodList->getAvailableMethods($quote) as $method) {
            $output[] = $this->paymentMethodConverter->toDataObject($method);
        }
        return $output;
    }
}
