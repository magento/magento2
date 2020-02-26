<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Ui\Component\Listing\Column\Cms;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Ui\Component\Listing\Column\Cms\Options
     */
    protected $options;

    /**
     * @var \Magento\Store\Model\System\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemStoreMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \Magento\Store\Model\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->systemStoreMock = $this->getMockBuilder(\Magento\Store\Model\System\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->websiteMock = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getId', 'getName']);

        $this->groupMock = $this->createMock(\Magento\Store\Model\Group::class);

        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);

        $this->options = $objectManager->getObject(
            \Magento\Cms\Ui\Component\Listing\Column\Cms\Options::class,
            [
                'systemStore' => $this->systemStoreMock,
                'escaper' => $this->escaperMock
            ]
        );
    }

    public function testToOptionArray()
    {
        $websiteCollection = [$this->websiteMock];
        $groupCollection = [$this->groupMock];
        $storeCollection = [$this->storeMock];

        $expectedOptions = [
            [
                'label' => __('All Store Views'),
                'value' => '0',
            ],
            [
                'label' => 'Main Website',
                'value' => [
                    [
                        'label' => '    Main Website Store',
                        'value' => [
                            [
                                'label' => '        Default Store View',
                                'value' => '1',
                                '__disableTmpl' => true,
                            ]
                        ],
                        '__disableTmpl' => true,
                    ]
                ],
                '__disableTmpl' => true,
            ]
        ];

        $this->systemStoreMock->expects($this->once())->method('getWebsiteCollection')->willReturn($websiteCollection);
        $this->systemStoreMock->expects($this->once())->method('getGroupCollection')->willReturn($groupCollection);
        $this->systemStoreMock->expects($this->once())->method('getStoreCollection')->willReturn($storeCollection);

        $this->websiteMock->expects($this->atLeastOnce())->method('getId')->willReturn('1');
        $this->websiteMock->expects($this->any())->method('getName')->willReturn('Main Website');

        $this->groupMock->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn('1');
        $this->groupMock->expects($this->atLeastOnce())->method('getId')->willReturn('1');
        $this->groupMock->expects($this->atLeastOnce())->method('getName')->willReturn('Main Website Store');

        $this->storeMock->expects($this->atLeastOnce())->method('getGroupId')->willReturn('1');
        $this->storeMock->expects($this->atLeastOnce())->method('getName')->willReturn('Default Store View');
        $this->storeMock->expects($this->atLeastOnce())->method('getId')->willReturn('1');

        $this->assertEquals($expectedOptions, $this->options->toOptionArray());
    }
}
