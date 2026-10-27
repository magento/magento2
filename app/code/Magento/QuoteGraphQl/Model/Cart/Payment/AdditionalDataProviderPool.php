<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

/**
 * Pool model for AdditionalDataProvider
 */
class AdditionalDataProviderPool
{
    /**
     * @var AdditionalDataProviderInterface[]
     */
    private $dataProviders;

    /**
     * @param array $dataProviders
     */
    public function __construct(array $dataProviders = [])
    {
        $this->dataProviders = $dataProviders;
    }

    /**
     * Return additional data for the payment method
     *
     * @param string $methodCode
     * @param array $data
     * @return array
     */
    public function getData(string $methodCode, array $data): array
    {
        $additionalData = [];
        if (isset($this->dataProviders[$methodCode])) {
            $additionalData = $this->dataProviders[$methodCode]->getData($data);
        }

        return $additionalData;
    }
}
