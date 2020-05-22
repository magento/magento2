<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Catalog\Model\Plugin\DefaultSortByUpdateCategorySavePlugin;
use PHPUnit\Framework\TestCase;

class DefaultSortByUpdateCategorySavePluginTest extends TestCase
{
    /**
     * @var string
     */
    private static $defaultSortByFromKey = 'default_sort_by';

    /**
     * Extensible DataObject Converter mock
     *
     * @var ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensibleDataObjectConverterMock;

    /**
     * CategoryInterface mock
     *
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryInterfaceMock;

    /**
     * CategoryRepositoryInterface mock
     *
     * @var CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * Category Save plugin
     *
     * @var DefaultSortByUpdateCategorySavePlugin
     */
    private $categorySavePlugin;

    protected function setUp()
    {
        $this->extensibleDataObjectConverterMock = $this
            ->getMockBuilder(ExtensibleDataObjectConverter::class)
            ->disableOriginalConstructor()
            ->setMethods(['toNestedArray'])
            ->getMock();

        $this->subjectMock = $this
            ->getMockBuilder(CategoryRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->categoryInterfaceMock = $this
            ->getMockBuilder(CategoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomAttribute'])
            ->getMockForAbstractClass();

        $this->categorySavePlugin = new DefaultSortByUpdateCategorySavePlugin(
            $this->extensibleDataObjectConverterMock
        );
    }

    /**
     * @return void
     */
    public function testBeforeSaveWithDefaultSortByData(): void
    {
        $existingData = [
            'parent_id' => 2,
            'name' => 'test category 002',
            'is_active' => true,
            'position' => 1,
            'level' => 2,
            'children' => '',
            'created_at' => '2020-03-05 22:04:01',
            'updated_at' => '2020-04-16 23:57:22',
            'available_sort_by' => [],
            'include_in_menu' => true,
            'default_sort_by' => [
                0 => 'position'
            ]
        ];
        $this->categoryInterfaceMock
            ->expects($this->once())
            ->method('setCustomAttribute')
            ->with(
                $this->equalTo(self::$defaultSortByFromKey),
                $existingData['default_sort_by'][0]
            )
            ->willReturnSelf();
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->with(
                $this->equalTo($this->categoryInterfaceMock),
                [],
                CategoryInterface::class
            )
            ->willReturn($existingData);
        $this->categorySavePlugin->beforeSave(
            $this->subjectMock,
            $this->categoryInterfaceMock
        );
    }
}
