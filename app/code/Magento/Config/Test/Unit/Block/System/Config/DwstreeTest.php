<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config;

class DwstreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Dwstree
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->context = $objectManager->getObject(
            \Magento\Backend\Block\Template\Context::class,
            [
                'request'      => $this->requestMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );

        $this->object = $objectManager->getObject(
            \Magento\Config\Block\System\Config\Dwstree::class,
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
            ->will(
                $this->returnValueMap(
                    [
                        ['section', $section],
                        ['website', $website['expected']['code']],
                        ['store', $store['expected']['code']],
                    ]
                )
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
                'store_'   . $store['actual']['code']
            ],
            $this->object->getTabsIds()
        );
    }

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
