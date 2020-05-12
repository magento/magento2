<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\EntityManager\Test\Unit;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\EntityManager\Mapper;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp(): void
    {
        $config = [
            CustomerInterface::class => ['entity_id' => 'id'],
            AddressInterface::class => ['parent_id' => 'customer_id', 'invalid' => '']
        ];
        $this->mapper = new Mapper($config);
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
            CustomerInterface::class,
            $inputData
        );

        $this->assertEquals($expectedOutput, $actualOutput);
    }

    public function testEntityToDatabaseException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Incorrect configuration for Magento\Customer\Api\Data\AddressInterface');
        $inputData = [
            'group_id' => 1,
            'extension_attributes' => ['extension_attribute' => ['value' => 'some value']],
        ];
        $this->mapper->entityToDatabase(AddressInterface::class, $inputData);
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
            CustomerInterface::class,
            $inputData
        );

        $this->assertEquals($expectedOutput, $actualOutput);
    }

    public function testDatabaseToEntityException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Incorrect configuration for Magento\Customer\Api\Data\AddressInterface');
        $inputData = [
            'group_id' => 1,
            'extension_attributes' => ['extension_attribute' => ['value' => 'some value']],
            'invalid' => 123
        ];
        $this->mapper->databaseToEntity(AddressInterface::class, $inputData);
    }
}
