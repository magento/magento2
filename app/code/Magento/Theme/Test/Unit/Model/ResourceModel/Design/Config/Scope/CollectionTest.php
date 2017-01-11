<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\ResourceModel\Design\Config\Scope;

use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Model\ResourceModel\Design\Config\Scope\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeTreeProviderInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Theme\Model\Design\Config\MetadataProviderInterface;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Collection */
    protected $collection;

    /** @var  EntityFactoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityFactoryMock;

    /** @var ScopeTreeProviderInterface|\PHPUnit_Framework_MockObject_MockObject*/
    protected $scopeTreeMock;

    /** @var MetadataProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $metadataProviderMock;

    /** @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $appConfigMock;

    /** @var \Magento\Theme\Model\Design\Config\ValueProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $valueProcessor;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Data\Collection\EntityFactoryInterface::class
        )->getMockForAbstractClass();
        $this->scopeTreeMock = $this->getMockBuilder(\Magento\Framework\App\ScopeTreeProviderInterface::class)
            ->getMockForAbstractClass();
        $this->metadataProviderMock =
            $this->getMockBuilder(\Magento\Theme\Model\Design\Config\MetadataProviderInterface::class)
                ->getMockForAbstractClass();
        $this->appConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->valueProcessor = $this->getMockBuilder(\Magento\Theme\Model\Design\Config\ValueProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->scopeTreeMock,
            $this->metadataProviderMock,
            $this->appConfigMock,
            $this->valueProcessor
        );
    }

    /**
     * Test loadData
     *
     * @return void
     */
    public function testLoadData()
    {
        $this->scopeTreeMock->expects($this->any())
            ->method('get')
            ->willReturn(
                [
                    'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    'scope_id' => null,
                    'scopes' => [
                       [
                           'scope' => ScopeInterface::SCOPE_WEBSITE,
                           'scope_id' => 1,
                           'scopes' => [
                               [
                                   'scope' => ScopeInterface::SCOPE_GROUP,
                                   'scope_id' => 1,
                                   'scopes' => [
                                       [
                                           'scope' => ScopeInterface::SCOPE_STORE,
                                           'scope_id' => 1,
                                           'scopes' => [],
                                        ],
                                   ],
                               ],
                           ],
                       ],
                   ],
                ]
            );

        $this->metadataProviderMock->expects($this->any())
            ->method('get')
            ->willReturn(
                [
                    'first_field' => ['path' => 'first/field/path', 'use_in_grid' => 0],
                    'second_field' => ['path' => 'second/field/path', 'use_in_grid' => true],
                    'third_field' => ['path' => 'third/field/path'],
                ]
            );

        $this->appConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['second/field/path', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, 'DefaultValue'],
                    ['second/field/path', ScopeInterface::SCOPE_WEBSITE, 1, 'WebsiteValue'],
                    ['second/field/path', ScopeInterface::SCOPE_STORE, 1, 'WebsiteValue'],
                ]
            );
        $this->valueProcessor->expects($this->atLeastOnce())
            ->method('process')
            ->withConsecutive(
                ['DefaultValue', 'default', null, ['path' => 'second/field/path', 'use_in_grid' => 1]],
                ['WebsiteValue', 'website', 1, ['path' => 'second/field/path', 'use_in_grid' => 1]],
                ['WebsiteValue', 'store', 1, ['path' => 'second/field/path', 'use_in_grid' => 1]]
            )
            ->willReturnOnConsecutiveCalls(
                'DefaultValue',
                'WebsiteValue',
                'WebsiteValue'
            );

        $expectedResult = [
            new \Magento\Framework\DataObject([
                'store_website_id' => null,
                'store_group_id' => null,
                'store_id' => null,
                'second_field' => 'DefaultValue'
            ]),
            new \Magento\Framework\DataObject([
                'store_website_id' => 1,
                'store_group_id' => null,
                'store_id' => null,
                'second_field' => 'WebsiteValue'
            ]),
            new \Magento\Framework\DataObject([
                'store_website_id' => 1,
                'store_group_id' => 1,
                'store_id' => 1,
                'second_field' => 'WebsiteValue' #parent (website level) value
            ]),
        ];

        $this->assertEquals($expectedResult, $this->collection->getItems());
    }
}
