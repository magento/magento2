<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\StoreGraphQl\Model\Resolver\Store\StoreConfigsDataProvider;

/**
 * StoreConfig page field resolver, used for GraphQL request processing.
 */
class StoreConfigsResolver implements ResolverInterface
{
    /**
     * @var StoreConfigsDataProvider
     */
    private $storeConfigsDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param StoreConfigsDataProvider $storeConfigsDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        StoreConfigsDataProvider $storeConfigsDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->valueFactory = $valueFactory;
        $this->storeConfigsDataProvider = $storeConfigsDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {

        $storeCodes = $this->getStoreCodes($args);
        $storeConfigsData = $this->storeConfigsDataProvider->getStoreConfigsByStoreCodes($storeCodes);

        $result = function () use ($storeConfigsData) {
            return !empty($storeConfigsData) ? $storeConfigsData : [];
        };

        return $this->valueFactory->create($result);
    }

    /**
     * Retrieve store codes
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    private function getStoreCodes($args)
    {
        if (isset($args['storeCodes'])) {
            if (is_array($args['storeCodes'])) {
                return $args['storeCodes'];
            }
            throw new GraphQlInputException(__('"store codes should contain a valid array'));
        }

        return null;
    }
}
