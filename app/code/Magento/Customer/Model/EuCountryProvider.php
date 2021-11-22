<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Directory\Controller\Adminhtml\Json\EuCountryProviderInterface;
use Magento\Directory\Controller\Adminhtml\Json\IsEuCountry;
use Magento\Framework\Serialize\SerializerInterface;

class EuCountryProvider implements EuCountryProviderInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Vat
     */
    protected $customerVat;

    /**
     * @param SerializerInterface $serializer
     * @param Vat                 $customerVat
     */
    public function __construct(
        SerializerInterface $serializer,
        Vat $customerVat
    ) {
        $this->serializer  = $serializer;
        $this->customerVat = $customerVat;
    }

    /**
     * @inheritDoc
     */
    public function isEuCountry($countryCode): string
    {
        $isEuCountry = false;

        if (!empty($countryCode)) {
            if ($this->customerVat->isCountryInEU($countryCode)) {
                $isEuCountry = true;
            }
        }

        return $this->serializer->serialize($isEuCountry);
    }
}
