<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCustomer\Model\Type\Handler;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\GraphQl\Model\EntityAttributeList;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\GraphQlEav\Model\Resolver\Query\Type;

/**
 * Define Customer GraphQL type
 */
class Customer implements HandlerInterface
{
    const CUSTOMER_TYPE_NAME = 'Customer';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var EntityAttributeList
     */
    private $entityAttributeList;

    /**
     * @var \Magento\Framework\GraphQl\TypeFactory
     */
    private $typeFactory;

    /**
     * @var Type
     */
    private $typeLocator;

    /**
     * @var CustomerMetadataInterface
     */
    private $metadataService;

    /**
     * @param Pool $typePool
     * @param TypeGenerator $typeGenerator
     * @param EntityAttributeList $entityAttributeList
     * @param TypeFactory $typeFactory
     * @param Type $typeLocator
     * @param CustomerMetadataInterface $metadataService
     */
    public function __construct(
        Pool $typePool,
        TypeGenerator $typeGenerator,
        EntityAttributeList $entityAttributeList,
        TypeFactory $typeFactory,
        Type $typeLocator,
        CustomerMetadataInterface $metadataService
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->entityAttributeList = $entityAttributeList;
        $this->typeFactory = $typeFactory;
        $this->typeLocator = $typeLocator;
        $this->metadataService = $metadataService;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->typeFactory->createObject(
            [
                'name' => self::CUSTOMER_TYPE_NAME,
                'fields' => $this->getFields(self::CUSTOMER_TYPE_NAME),
            ]
        );
    }

    /**
     * Retrieve the customer base fields
     *
     * @param string $typeName
     * @return array
     * @throws \LogicException Schema failed to generate from service contract type name
     */
    private function getFields(string $typeName)
    {
        $result = [];
        $customerEntityType = \Magento\Customer\Model\Customer::ENTITY;
        $attributes = $this->entityAttributeList->getDefaultEntityAttributes(
            $customerEntityType,
            $this->metadataService
        );
        foreach (array_keys($attributes) as $attributeCode) {
            $locatedType = $this->typeLocator->getType(
                $attributeCode,
                $customerEntityType
            ) ?: Pool::TYPE_STRING;
            $locatedType = $locatedType === TypeProcessor::NORMALIZED_ANY_TYPE ? Pool::TYPE_STRING : $locatedType;
            $result[$attributeCode] = $locatedType;
        }

        $staticAttributes = $this->typeGenerator->getTypeData('CustomerDataCustomerInterface');
        $result = array_merge($result, $staticAttributes);

        //customer secure filtering according to @see \Magento\Customer\Model\Data\CustomerSecure
        unset($result['rp_token']);
        unset($result['rp_token_created_at']);
        unset($result['password_hash']);
        unset($result['deleteable']);

        //additional filtering that we don't need in the schema
        unset($result['first_failure']);
        unset($result['lock_expires']);
        unset($result['failures_num']);
        unset($result['extension_attribute']);

        unset($result['confirmation']);
        unset($result['website_id']);
        unset($result['store_id']);
        unset($result['created_in']);
        unset($result['disable_auto_group_change']);
        unset($result['updated_at']);
        unset($result['gender']);
        
        $resolvedTypes = $this->typeGenerator->generate($typeName, $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
