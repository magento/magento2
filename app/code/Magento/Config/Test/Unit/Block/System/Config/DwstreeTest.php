<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Dwstree;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DwstreeTest extends TestCase
{
    /**
     * @var Dwstree
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $websiteMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $context;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request'      => $this->requestMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );

        $this->object = $objectManager->getObject(
            Dwstree::class,
            ['context' => $this->context]
        );
    }

    /**
     * @param $section
     * @param $website
     * @param $store
     * @dataProvider initTabsDataProvider
     */
    public function testInitTabs($section, $website, $store)
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['section', $section],
                    ['website', $website['expected']['code']],
                    ['store', $store['expected']['code']],
                ]
            );
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())
            ->method('getCode')
            ->willReturn($website['actual']['code']);
        $this->websiteMock->expects($this->any())
            ->method('getName')
            ->willReturn($website['expected']['name']);
        $this->websiteMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$this->storeMock]);
        $this->storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn($store['actual']['code']);
        $this->storeMock->expects($this->any())
            ->method('getName')
            ->willReturn($store['actual']['name']);

        $this->assertEquals($this->object, $this->object->initTabs());

        $this->assertEquals(
            [
                'default',
                'website_' . $website['actual']['code'],
                'store_' . $store['actual']['code']
            ],
            $this->object->getTabsIds()
        );
    }

    /**
     * @return array
     */
    public function initTabsDataProvider()
    {
        return [
            'matchAll'  => [
                'scope'   => 'Test Scope',
                'website' => [
                    'expected' => ['name' => 'Test Website Name', 'code' => 'Test Website Code'],
                    'actual'   => ['name' => 'Test Website Name', 'code' => 'Test Website Code'],
                ],
                'store'   => [
                    'expected' => ['name' => 'Test   Store Name', 'code' => 'Test   Store Code'],
                    'actual'   => ['name' => 'Test   Store Name', 'code' => 'Test   Store Code'],
                ],
            ],
            'matchStore'  => [
                'scope'   => 'Test Scope',
                'website' => [
                    'expected' => ['name' => 'Test Website Name', 'code' => 'Test Website Code'],
                    'actual'   => ['name' => false, 'code' => false],
                ],
                'store'   => [
                    'expected' => ['name' => 'Test   Store Name', 'code' => 'Test   Store Code'],
                    'actual'   => ['name' => 'Test   Store Name', 'code' => 'Test   Store Code'],
                ],
            ],
            'matchWebsite'  => [
                'scope'   => 'Test Scope',
                'website' => [
                    'expected' => ['name' => 'Test Website Name', 'code' => 'Test Website Code'],
                    'actual'   => ['name' => 'Test Website Name', 'code' => 'Test Website Code'],
                ],
                'store'   => [
                    'expected' => ['name' => 'Test   Store Name', 'code' => 'Test   Store Code'],
                    'actual'   => ['name' => false, 'code' => false],
                ],
            ],
            'noMatch'  => [
                'scope'   => 'Test Scope',
                'website' => [
                    'expected' => ['name' => 'Test Website Name', 'code' => 'Test Website Code'],
                    'actual'   => ['name' => false, 'code' => false],
                ],
                'store'   => [
                    'expected' => ['name' => 'Test   Store Name', 'code' => 'Test   Store Code'],
                    'actual'   => ['name' => false, 'code' => false],
                ],
            ],
        ];
    }
}
