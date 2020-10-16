<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Source;

use Magento\Customer\Model\Customer\Attribute\Source\Website;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Store\Model\System\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    /** @var Website */
    protected $model;

    /** @var CollectionFactory|MockObject */
    protected $collectionFactoryMock;

    /** @var OptionFactory|MockObject */
    protected $optionFactoryMock;

    /** @var Store|MockObject */
    protected $storeMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->optionFactoryMock =
            $this->getMockBuilder(OptionFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Website(
            $this->collectionFactoryMock,
            $this->optionFactoryMock,
            $this->storeMock
        );
    }

    /**
     * Mock website options
     *
     * @return array
     */
    protected function mockOptions()
    {
        $options = [
            [
                'value' => 'value1',
                'label' => 'label1',
            ],
            [
                'value' => 'value2',
                'label' => 'label2',
            ],
        ];

        $this->storeMock->expects($this->once())
            ->method('getWebsiteValuesForForm')
            ->with(false, false)
            ->willReturn($options);

        return $options;
    }

    public function testGetAllOptions()
    {
        $options = $this->mockOptions();

        $this->assertEquals($options, $this->model->getAllOptions());
        // Check the options are cached
        $this->assertEquals($options, $this->model->getAllOptions());
    }

    public function testGetOptionText()
    {
        $this->mockOptions();

        $this->assertEquals('label1', $this->model->getOptionText('value1'));
    }

    public function testGetOptionTextWithoutOption()
    {
        $this->mockOptions();

        $this->assertFalse($this->model->getOptionText('value'));
    }
}
