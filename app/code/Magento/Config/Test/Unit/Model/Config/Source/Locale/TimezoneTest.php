<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Source\Locale;

use Magento\Config\Model\Config\Source\Locale\Timezone;
use Magento\Framework\Locale\TranslatedLists;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TimezoneTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $listMock;

    /**
     * @var Timezone
     */
    protected $model;

    protected function setUp(): void
    {
        $this->listMock = $this->getMockBuilder(TranslatedLists::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Timezone($this->listMock);
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
