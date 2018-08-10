<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface as Quote;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Service is responsible for handling failed payment transactions.
 *
 * It depends on Stores > Configuration > Sales > Checkout > Payment Failed Emails configuration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentFailuresService implements PaymentFailuresInterface
{
    /**
     * Store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param TimezoneInterface $localeDate
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        TimezoneInterface $localeDate,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->localeDate = $localeDate;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Sends an email about failed transaction.
     *
     * @param int $cartId
     * @param string $message
     * @param string $checkoutType
     * @return PaymentFailuresInterface
     */
    public function handle(
        int $cartId,
        string $message,
        string $checkoutType = 'onepage'
    ): PaymentFailuresInterface {
        $this->inlineTranslation->suspend();
        $quote = $this->cartRepository->get($cartId);

        $template = $this->getConfigValue('checkout/payment_failed/template', $quote);
        $receiver = $this->getConfigValue('checkout/payment_failed/receiver', $quote);
        $sendTo = [
            [
                'email' => $this->getConfigValue('trans_email/ident_' . $receiver . '/email', $quote),
                'name' => $this->getConfigValue('trans_email/ident_' . $receiver . '/name', $quote),
            ],
        ];

        $copyMethod = $this->getConfigValue('checkout/payment_failed/copy_method', $quote);
        $copyTo = $this->getConfigEmails($quote);

        $bcc = [];
        if (!empty($copyTo)) {
            switch ($copyMethod) {
                case 'bcc':
                    $bcc = $copyTo;
                    break;
                case 'copy':
                    foreach ($copyTo as $email) {
                        $sendTo[] = ['email' => $email, 'name' => null];
                    }
                    break;
            }
        }

        foreach ($sendTo as $recipient) {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions([
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars($this->getTemplateVars($quote, $message, $checkoutType))
                ->setFrom($this->getSendFrom($quote))
                ->addTo($recipient['email'], $recipient['name'])
                ->addBcc($bcc)
                ->getTransport();

            try {
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Returns mail template variables.
     *
     * @param Quote $quote
     * @param string $message
     * @param string $checkoutType
     * @return array
     */
    private function getTemplateVars(Quote $quote, string $message, string $checkoutType): array
    {
        return [
            'reason' => $message,
            'checkoutType' => $checkoutType,
            'dateAndTime' => $this->getLocaleDate(),
            'customer' => $this->getCustomerName($quote),
            'customerEmail' => $quote->getBillingAddress()->getEmail(),
            'billingAddress' => $quote->getBillingAddress(),
            'shippingAddress' => $quote->getShippingAddress(),
            'shippingMethod' => $this->getConfigValue(
                'carriers/' . $this->getShippingMethod($quote) . '/title',
                $quote
            ),
            'paymentMethod' => $this->getConfigValue(
                'payment/' . $this->getPaymentMethod($quote) . '/title',
                $quote
            ),
            'items' => implode('<br />', $this->getQuoteItems($quote)),
            'total' => $quote->getCurrency()->getStoreCurrencyCode() . ' ' . $quote->getGrandTotal(),
        ];
    }

    /**
     * Returns scope config value by config path.
     *
     * @param string $configPath
     * @param Quote $quote
     * @return mixed
     */
    private function getConfigValue(string $configPath, Quote $quote)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );
    }

    /**
     * Returns shipping method from quote.
     *
     * @param Quote $quote
     * @return string
     */
    private function getShippingMethod(Quote $quote)
    {
        $shippingMethod = '';
        if ($shippingInfo = $quote->getShippingAddress()->getShippingMethod()) {
            $data = explode('_', $shippingInfo);
            $shippingMethod = $data[0];
        }

        return $shippingMethod;
    }

    /**
     * Returns payment method title from quote.
     *
     * @param Quote $quote
     * @return string
     */
    private function getPaymentMethod(Quote $quote)
    {
        $paymentMethod = '';
        if ($paymentInfo = $quote->getPayment()) {
            $paymentMethod = $paymentInfo->getMethod();
        }

        return $paymentMethod;
    }

    /**
     * Returns quote visible items.
     *
     * @param Quote $quote
     * @return array
     */
    private function getQuoteItems(Quote $quote): array
    {
        $items = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $itemData = $item->getProduct()->getName() . '  x ' . $item->getQty() . '  ' .
                        $quote->getCurrency()->getStoreCurrencyCode() . ' ' .
                        $item->getProduct()->getFinalPrice($item->getQty());
            $items[] = $itemData;
        }

        return $items;
    }

    /**
     * Gets email values by configuration path.
     *
     * @param Quote $quote
     * @return array|false
     */
    private function getConfigEmails(Quote $quote)
    {
        $configData = $this->getConfigValue('checkout/payment_failed/copy_to', $quote);
        if (!empty($configData)) {
            return explode(',', $configData);
        }

        return false;
    }

    /**
     * Returns sender identity.
     *
     * @param Quote $quote
     * @return string
     */
    private function getSendFrom(Quote $quote): string
    {
        return $this->getConfigValue('checkout/payment_failed/identity', $quote);
    }

    /**
     * Returns current locale date and time
     *
     * @return string
     */
    private function getLocaleDate(): string
    {
        return $this->localeDate->formatDateTime(
            new \DateTime(),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
    }

    /**
     * Returns customer name.
     *
     * @param Quote $quote
     * @return string
     */
    private function getCustomerName(Quote $quote): string
    {
        $customer = __('Guest');
        if (!$quote->getCustomerIsGuest()) {
            $customer = $quote->getCustomer()->getFirstname() . ' ' .
                        $quote->getCustomer()->getLastname();
        }

        return $customer;
    }
}
