<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Ui\Component\Listing\Column\Cms;

use Magento\Cms\Ui\Component\Listing\Column\Cms\Options;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Group;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * @var \Magento\Store\Model\System\Store|MockObject
     */
    protected $systemStoreMock;

    /**
     * @var \Magento\Store\Model\Website|MockObject
     */
    protected $websiteMock;

    /**
     * @var Group|MockObject
     */
    protected $groupMock;

    /**
     * @var \Magento\Store\Model\Store|MockObject
     */
    protected $storeMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->systemStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->websiteMock = $this->createPartialMock(Website::class, ['getId', 'getName']);

        $this->groupMock = $this->createMock(Group::class);

        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->escaperMock = $this->createMock(Escaper::class);

        $this->options = $objectManager->getObject(
            Options::class,
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
                'value' => '0'
            ],
            [
                'label' => 'Main Website',
                'value' => [
                    [
                        'label' => '    Main Website Store',
                        'value' => [
                            [
                                'label' => '        Default Store View',
                                'value' => '1'
                            ]
                        ]
                    ]
                ]
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

        $this->escaperMock->expects($this->atLeastOnce())->method('escapeHtml')->willReturnMap(
            [
                ['Default Store View', null, 'Default Store View'],
                ['Main Website Store', null, 'Main Website Store'],
                ['Main Website', null, 'Main Website']
            ]
        );

        $this->assertEquals($expectedOptions, $this->options->toOptionArray());
    }
}
