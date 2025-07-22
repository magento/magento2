<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\CountryInformationInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Country field resolver, used for GraphQL request processing.
 */
class Country implements ResolverInterface
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
        ?array $value = null,
        ?array $args = null
    ) {
        if (empty($args['id'])) {
            throw new GraphQlInputException(__('Country "id" value should be specified'));
        }

        try {
            $country = $this->countryInformationAcquirer->getCountryInfo($args['id']);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()), $exception);
        }

        return $this->dataProcessor->buildOutputDataArray(
            $country,
            CountryInformationInterface::class
        );
    }
}
