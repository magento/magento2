<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Block\Source;

use Magento\Cms\Model\Block;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IsActiveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Block|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsBlockMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Block\Source\IsActive
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->cmsBlockMock = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAvailableStatuses'])
            ->getMock();

        $this->object = $this->objectManagerHelper->getObject($this->getSourceClassName(), [
            'cmsBlock' => $this->cmsBlockMock,
        ]);
    }

    /**
     * @return string
     */
    protected function getSourceClassName()
    {
        return \Magento\Cms\Model\Block\Source\IsActive::class;
    }

    /**
     * @param array $availableStatuses
     * @param array $expected
     * @return void
     * @dataProvider getAvailableStatusesDataProvider
     */
    public function testToOptionArray(array $availableStatuses, array $expected)
    {
        $this->cmsBlockMock->expects($this->once())
            ->method('getAvailableStatuses')
            ->willReturn($availableStatuses);

        $this->assertSame($expected, $this->object->toOptionArray());
    }

    /**
     * @return array
     */
    public function getAvailableStatusesDataProvider()
    {
        return [
            [
                [],
                [],
            ],
            [
                ['testStatus' => 'testValue'],
                [['label' => 'testValue', 'value' => 'testStatus']],
            ],
        ];
    }
}
