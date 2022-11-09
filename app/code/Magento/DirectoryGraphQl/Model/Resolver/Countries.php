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
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\CountryInformationInterface;

/**
 * Countries field resolver, used for GraphQL request processing.
 */
class Countries implements ResolverInterface
{
    /**
     * @var DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
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
        $countries = $this->countryInformationAcquirer->getCountriesInfo();

        $output = [];
        foreach ($countries as $country) {
            if (!empty($country->getFullNameLocale())) {
                $output[] = $this->dataProcessor->buildOutputDataArray($country, CountryInformationInterface::class);
            }
        }

        return $output;
    }
}
