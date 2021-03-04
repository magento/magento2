<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Source\Locale;

class TimezoneTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $listMock;

    /**
     * @var \Magento\Config\Model\Config\Source\Locale\Timezone
     */
    protected $model;

    protected function setUp(): void
    {
        $this->listMock = $this->getMockBuilder(\Magento\Framework\Locale\TranslatedLists::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Config\Model\Config\Source\Locale\Timezone($this->listMock);
    }

    public function testToOptionArray()
    {
        $ignoredTimezones = [
            'Antarctica/Troll',
            'Asia/Chita',
            'Asia/Srednekolymsk',
            'Pacific/Bougainville'
        ];
        $list = \DateTimeZone::listIdentifiers();
        $preparedList = [];
        foreach ($list as $value) {
            $preparedList[] = ['value' => $value, 'label' => $value];
        }
        $this->listMock->expects($this->once())
            ->method('getOptionTimezones')
            ->willReturn($preparedList);
        $result = $this->model->toOptionArray();
        foreach ($result as $value) {
            if (in_array($value['value'], $ignoredTimezones)) {
                $this->fail('Locale ' . $value['value'] . ' shouldn\'t be presented');
            }
        }
    }
}
