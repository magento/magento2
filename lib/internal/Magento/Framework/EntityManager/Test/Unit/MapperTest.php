<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Test\Unit;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\EntityManager\Mapper
     */
    private $mapper;

    protected function setUp(): void
    {
        $config = [
            \Magento\Customer\Api\Data\CustomerInterface::class => ['entity_id' => 'id'],
            \Magento\Customer\Api\Data\AddressInterface::class => ['parent_id' => 'customer_id', 'invalid' => '']
        ];
        $this->mapper = new \Magento\Framework\EntityManager\Mapper($config);
    }

    public function testEntityToDatabase()
    {
        $inputData = [
            'group_id' => 1,
            'extension_attributes' => ['extension_attribute' => ['value' => 'some value']],
            'id' => 123
        ];
        $expectedOutput = $inputData;
        $expectedOutput['entity_id'] = 123;
        unset($expectedOutput['id']);

        $actualOutput = $this->mapper->entityToDatabase(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            $inputData
        );

        $this->assertEquals($expectedOutput, $actualOutput);
    }

    /**
     */
    public function testEntityToDatabaseException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Incorrect configuration for Magento\\Customer\\Api\\Data\\AddressInterface');

        $inputData = [
            'group_id' => 1,
            'extension_attributes' => ['extension_attribute' => ['value' => 'some value']],
        ];
        $this->mapper->entityToDatabase(\Magento\Customer\Api\Data\AddressInterface::class, $inputData);
    }

    public function testDatabaseToEntity()
    {
        $inputData = [
            'group_id' => 1,
            'extension_attributes' => ['extension_attribute' => ['value' => 'some value']],
            'entity_id' => 123
        ];
        $expectedOutput = $inputData;
        $expectedOutput['id'] = 123;
        unset($expectedOutput['entity_id']);

        $actualOutput = $this->mapper->databaseToEntity(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            $inputData
        );

        $this->assertEquals($expectedOutput, $actualOutput);
    }

    /**
     */
    public function testDatabaseToEntityException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Incorrect configuration for Magento\\Customer\\Api\\Data\\AddressInterface');

        $inputData = [
            'group_id' => 1,
            'extension_attributes' => ['extension_attribute' => ['value' => 'some value']],
            'invalid' => 123
        ];
        $this->mapper->databaseToEntity(\Magento\Customer\Api\Data\AddressInterface::class, $inputData);
    }
}
