<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

use Magento\Framework\Api\Fixture\DataObjectInterface;
use Magento\Framework\Api\Fixture\DataObjectFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DataObjectHelperTest extends TestCase
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
        $this->dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);
    }

    /**
     * Test object is populated with data from array.
     *
     * @return void
     */
    public function testPopulateWithArray(): void
    {
        $inputArray = [
            'first_a_second' => '1',
            'first_at_second' => '1',
            'first_a_t_m_second' => '1',
            'random_attribute' => 'random'
        ];
        $expectedData = [
            'first_a_second' => '1',
            'first_at_second' => '1',
            'first_a_t_m_second' => '1',
        ];
        $object = $this->dataObjectFactory->create();
        $this->dataObjectHelper->populateWithArray($object, $inputArray, DataObjectInterface::class);
        $this->assertEquals($expectedData, $object->getData());
    }
}
