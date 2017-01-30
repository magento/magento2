<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Source;

use Magento\Customer\Model\Customer\Attribute\Source\Website;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /** @var Website */
    protected $model;

    /** @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionFactoryMock;

    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $optionFactoryMock;

    /** @var \Magento\Store\Model\System\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeMock;

    protected function setUp()
    {
        $this->collectionFactoryMock =
            $this->getMockBuilder('Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionFactoryMock =
            $this->getMockBuilder('Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder('Magento\Store\Model\System\Store')
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

        $this->assertEquals(false, $this->model->getOptionText('value'));
    }
}
