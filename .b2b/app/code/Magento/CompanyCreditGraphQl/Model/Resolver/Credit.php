<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompanyCreditGraphQl\Model\Resolver;

use Magento\CompanyCredit\Api\CreditDataProviderInterface;
use Magento\CompanyCredit\Model\PaymentMethodStatus;
use Magento\CompanyCreditGraphQl\Model\Credit\Balance;
use Magento\CompanyGraphQl\Model\Company\ResolverAccess;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\NegotiableQuote\Model\PriceCurrency;

/**
 * Company credit resolver
 */
class Credit implements ResolverInterface
{
    /**
     * @var ResolverAccess
     */
    private $resolverAccess;

    /**
     * @var array
     */
    private $allowedResources;

    /**
     * @var CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var PaymentMethodStatus
     */
    private $paymentMethodStatus;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param ResolverAccess $resolverAccess
     * @param CreditDataProviderInterface $creditDataProvider
     * @param PaymentMethodStatus $paymentMethodStatus
     * @param Balance $balance
     * @param PriceCurrency $priceCurrency
     * @param array $allowedResources
     */
    public function __construct(
        ResolverAccess $resolverAccess,
        CreditDataProviderInterface $creditDataProvider,
        PaymentMethodStatus $paymentMethodStatus,
        Balance $balance,
        PriceCurrency $priceCurrency,
        array $allowedResources = []
    ) {
        $this->resolverAccess = $resolverAccess;
        $this->allowedResources = $allowedResources;
        $this->creditDataProvider = $creditDataProvider;
        $this->paymentMethodStatus = $paymentMethodStatus;
        $this->balance = $balance;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $this->resolverAccess->isAllowed($this->allowedResources);

        if (!$this->paymentMethodStatus->isEnabled()) {
            throw new GraphQlInputException(__('"Payment on Account" is disabled.'));
        }

        $company = $value['model'];
        $credit = $this->creditDataProvider->get($company->getId());
        $currencyCode = $credit->getCurrencyCode();

        return [
            'outstanding_balance' => $this->balance->formatData($currencyCode, (float)$credit->getBalance(), $this->priceCurrency->format((float)$credit->getBalance(),false,null,null,$currencyCode)),
            'available_credit' => $this->balance->formatData($currencyCode, (float)$credit->getAvailableLimit(), $this->priceCurrency->format((float)$credit->getAvailableLimit(),false,null,null,$currencyCode)),
            'credit_limit' => $this->balance->formatData($currencyCode, (float)$credit->getCreditLimit(), $this->priceCurrency->format((float)$credit->getCreditLimit(),false,null,null,$currencyCode))
        ];
    }
}
