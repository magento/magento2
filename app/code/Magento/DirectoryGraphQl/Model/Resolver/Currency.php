<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Directory\Api\CurrencyInformationAcquirerInterface;
use Magento\Directory\Api\Data\CurrencyInformationInterface;

/**
 * Currency field resolver, used for GraphQL request processing.
 */
class Currency implements ResolverInterface
{
    /**
     * @var DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var CurrencyInformationAcquirerInterface
     */
    private $currencyInformationAcquirer;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CurrencyInformationAcquirerInterface $currencyInformationAcquirer
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CurrencyInformationAcquirerInterface $currencyInformationAcquirer
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->currencyInformationAcquirer = $currencyInformationAcquirer;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return $this->dataProcessor->buildOutputDataArray(
            $this->currencyInformationAcquirer->getCurrencyInfo(),
            CurrencyInformationInterface::class
        );
    }
}
