<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCustomer\Model\Type\Handler;

use Magento\Eav\Api\AttributeManagementInterface;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Define Customer GraphQL type
 */
class Customer implements HandlerInterface
{
    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var AttributeManagementInterface
     */
    private $management;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param Pool $typePool
     * @param TypeGenerator $typeGenerator
     * @param AttributeManagementInterface $management
     * @param TypeFactory $typeFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        Pool $typePool,
        TypeGenerator $typeGenerator,
        AttributeManagementInterface $management,
        TypeFactory $typeFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->management = $management;
        $this->typeFactory = $typeFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return $this->typeFactory->createObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields($reflector->getShortName()),
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
        $eavSetup = $this->eavSetupFactory->create();
        $customerEntityCode = \Magento\Customer\Model\Customer::ENTITY;
        $defaultAttributeSetId = $eavSetup->getDefaultAttributeSetId($customerEntityCode);

        $result = [];
        $attributes = $this->management->getAttributes($customerEntityCode, $defaultAttributeSetId);
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = 'string';
        }

        $staticAttributes = $this->typeGenerator->getTypeData('CustomerDataCustomerInterface');
        $result = array_merge($result, $staticAttributes);

        unset($result['extension_attribute']);

        $resolvedTypes = $this->typeGenerator->generate($typeName, $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
