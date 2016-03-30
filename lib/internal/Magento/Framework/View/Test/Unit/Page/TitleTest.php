<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Page\Config
 */
namespace Magento\Framework\View\Test\Unit\Page;

use Magento\Store\Model\ScopeInterface;

class TitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $title;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->title = $objectManagerHelper->getObject(
            'Magento\Framework\View\Page\Title',
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @return void
     */
    public function testSet()
    {
        $value = 'test_value';
        $this->title->set($value);
        $this->assertEquals($value, $this->title->get());
    }

    /**
     * @return void
     */
    public function testUnset()
    {
        $value = 'test';
        $this->title->set($value);
        $this->assertEquals($value, $this->title->get());
        $this->title->unsetValue();
        $this->assertEmpty($this->title->get());
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $value = 'test';
        $prefix = 'prefix';
        $suffix = 'suffix';
        $expected = 'prefix test suffix';

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['design/head/title_prefix', ScopeInterface::SCOPE_STORE, null, $prefix],
                    ['design/head/title_suffix', ScopeInterface::SCOPE_STORE, null, $suffix],
                ]
            );
        $this->title->set($value);
        $this->assertEquals($expected, $this->title->get());
    }

    /**
     * @return void
     */
    public function testGetShort()
    {
        $value = 'some_title';
        $this->title->set($value);
        $this->title->prepend($value);
        $this->title->append($value);

        $this->assertEquals($value, $this->title->getShort());
    }

    /**
     * @return void
     */
    public function testGetShortWithSuffixAndPrefix()
    {
        $value = 'some_title';
        $prefix = 'prefix';
        $suffix = 'suffix';
        $expected = $prefix . ' ' . $value . ' ' . $suffix;
        $this->title->set($value);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['design/head/title_prefix', ScopeInterface::SCOPE_STORE, null, $prefix],
                    ['design/head/title_suffix', ScopeInterface::SCOPE_STORE, null, $suffix],
                ]
            );

        $this->assertEquals($expected, $this->title->getShort());
    }

    /**
     * @return void
     */
    public function testGetShortHeading()
    {
        $value = 'some_title';
        $this->title->set($value);

        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');

        $this->assertEquals($value, $this->title->getShortHeading());
    }

    /**
     * @return void
     */
    public function testGetDefault()
    {
        $defaultTitle = 'default title';
        $prefix = 'prefix';
        $suffix = 'suffix';
        $expected = 'prefix default title suffix';

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['design/head/title_prefix', ScopeInterface::SCOPE_STORE, null, $prefix],
                    ['design/head/title_suffix', ScopeInterface::SCOPE_STORE, null, $suffix],
                    ['design/head/default_title', ScopeInterface::SCOPE_STORE, null, $defaultTitle],
                ]
            );
        $this->assertEquals($expected, $this->title->getDefault());
    }

    /**
     * @return void
     */
    public function testAppendPrepend()
    {
        $value = 'title';
        $prepend = 'prepend_title';
        $append = 'append_title';
        $expected = 'prepend_title / title / append_title';

        $this->title->set($value);
        $this->title->prepend($prepend);
        $this->title->append($append);

        $this->assertEquals($expected, $this->title->get());
    }
}
