<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Block\Source;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\Block\Source\IsActive;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IsActiveTest extends TestCase
{
    /**
     * @var Block|MockObject
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
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->cmsBlockMock = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAvailableStatuses'])
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
        return IsActive::class;
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
    public static function getAvailableStatusesDataProvider()
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
