<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Data;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for customer data model
 */
class CustomerTest extends TestCase
{
    /** @var Customer */
    protected $model;

    /** @var ObjectManager */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->model = $this->objectManager->getObject(Customer::class);
    }

    /**
     * Test getGroupId()
     *
     * @return void
     */
    public function testGetGroupId()
    {
        $testGroupId = 3;
        $this->model->setGroupId($testGroupId);
        $this->assertEquals($testGroupId, $this->model->getGroupId());
    }

    /**
     * Test getCreatedIn()
     *
     * @param array|string $options
     * @param array $expectedResult
     *
     * @dataProvider getCreatedInDataProvider
     *
     * @return void
     */
    public function testGetCreatedIn($options, $expectedResult)
    {
        $optionsCount = count($options);
        $expectedCount = count($expectedResult);

        for ($i = 0; $i < $optionsCount; $i++) {
            $this->model->setCreatedIn($options[$i]);
            for ($j = $i; $j < $expectedCount; $j++) {
                $this->assertEquals($expectedResult[$j], $this->model->getCreatedIn());
                break;
            }
        }
    }

    /**
     * Data provider for testGetCreatedIn
     *
     * @return array
     */
    public function getCreatedInDataProvider()
    {
        return [
            'array' => [
                'options' => ['Default', 'Admin', 'US'],
                'expectedResult' => ['Default', 'Admin', 'US']
            ]
        ];
    }
}
