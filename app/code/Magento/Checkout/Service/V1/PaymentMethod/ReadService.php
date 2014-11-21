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

/**
 * Payment method read service object.
 */
class ReadService implements ReadServiceInterface
{
    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Quote method converter.
     *
     * @var QuoteMethodConverter
     */
    protected $quoteMethodConverter;

    /**
     * Payment method converter.
     *
     * @var PaymentMethodConverter
     */
    protected $paymentMethodConverter;

    /**
     * Method list.
     *
     * @var MethodList
     */
    protected $methodList;

    /**
     * Constructs a payment method read service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param QuoteMethodConverter $quoteMethodConverter Quote method converter.
     * @param PaymentMethodConverter $paymentMethodConverter Payment method converter.
     * @param MethodList $methodList Method list.
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
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\PaymentMethod  Payment method object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getPayment($cartId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $payment = $quote->getPayment();
        if (!$payment->getId()) {
            return null;
        }
        return $this->quoteMethodConverter->toDataObject($payment);
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\PaymentMethod[] Array of payment methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getList($cartId)
    {
        $output = [];
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        foreach ($this->methodList->getAvailableMethods($quote) as $method) {
            $output[] = $this->paymentMethodConverter->toDataObject($method);
        }
        return $output;
    }
}
