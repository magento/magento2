<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

class AdditionalDataProviderPool
{
    /**
     * @var AdditionalDataProviderInterface[]
     */
    private $dataProviders;

    public function __construct(array $dataProviders = [])
    {
        $this->dataProviders = $dataProviders;
    }

    public function getData(string $methodCode, array $args): array
    {
        $additionalData = [];
        if (isset($this->dataProviders[$methodCode])) {
            $additionalData = $this->dataProviders[$methodCode]->getData($args);
        }

        return $additionalData;
    }
}
