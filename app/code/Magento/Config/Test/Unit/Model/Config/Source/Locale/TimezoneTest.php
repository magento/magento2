<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Locale;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $listMock;

    /**
     * @var \Magento\Config\Model\Config\Source\Locale\Timezone
     */
    protected $model;

    protected function setUp()
    {
        $this->listMock = $this->getMockBuilder('Magento\Framework\Locale\TranslatedLists')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Config\Model\Config\Source\Locale\Timezone($this->listMock);
    }

    public function testToOptionArray()
    {
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
            if (in_array($value['value'], \Magento\Config\Model\Config\Source\Locale\Timezone::IGNORED_TIMEZONES)) {
                $this->fail('Locale ' . $value['value'] . ' shouldn\'t be presented');
            }
        }
    }
}
