<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCustomer\Model\Type\Handler;

use Magento\GraphQl\Model\EntityAttributeList;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

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
     * @param Pool $typePool
     * @param TypeGenerator $typeGenerator
     * @param EntityAttributeList $entityAttributeList
     * @param \Magento\Framework\GraphQl\TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        TypeGenerator $typeGenerator,
        EntityAttributeList $entityAttributeList,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->entityAttributeList = $entityAttributeList;
        $this->typeFactory = $typeFactory;
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
     * Retrieve Product base fields
     *
     * @param string $typeName
     * @return array
     * @throws \LogicException Schema failed to generate from service contract type name
     */
    private function getFields(string $typeName)
    {
        $result = [];
        $attributes = $this->entityAttributeList->getDefaultEntityAttributes(\Magento\Customer\Model\Customer::ENTITY);
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = 'string';
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
