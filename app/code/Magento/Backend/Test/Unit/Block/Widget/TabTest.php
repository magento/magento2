<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget;

class TabTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helper;

    protected function setUp()
    {
        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @param string $method
     * @param string $field
     * @param mixed $value
     * @param mixed $expected
     * @dataProvider dataProvider
     */
    public function testGetters($method, $field, $value, $expected)
    {
        /** @var \Magento\Backend\Block\Widget\Tab $object */
        $object = $this->helper->getObject(
            \Magento\Backend\Block\Widget\Tab::class,
            ['data' => [$field => $value]]
        );
        $this->assertEquals($expected, $object->{$method}());
    }

    public function dataProvider()
    {
        return [
            'getTabLabel' => ['getTabLabel', 'label', 'test label', 'test label'],
            'getTabLabel (default)' => ['getTabLabel', 'empty', 'test label', null],
            'getTabTitle' => ['getTabTitle', 'title', 'test title', 'test title'],
            'getTabTitle (default)' => ['getTabTitle', 'empty', 'test title', null],
            'canShowTab' => ['canShowTab', 'can_show', false, false],
            'canShowTab (default)' => ['canShowTab', 'empty', false, true],
            'isHidden' => ['isHidden', 'is_hidden', true, true],
            'isHidden (default)' => ['isHidden', 'empty', true, false],
            'getTabClass' => ['getTabClass', 'class', 'test classes', 'test classes'],
            'getTabClass (default)' => ['getTabClass', 'empty', 'test classes', null],
            'getTabUrl' => ['getTabUrl', 'url', 'test url', 'test url'],
            'getTabUrl (default)' => ['getTabUrl', 'empty', 'test url', '#']
        ];
    }
}
