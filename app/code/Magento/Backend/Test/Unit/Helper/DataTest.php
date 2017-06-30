<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontResolverMock;

    protected function setUp()
    {
        $this->_frontResolverMock = $this->getMock(
            \Magento\Backend\App\Area\FrontNameResolver::class,
            [],
            [],
            '',
            false
        );
        $this->_helper = new \Magento\Backend\Helper\Data(
            $this->getMock(\Magento\Framework\App\Helper\Context::class, [], [], '', false, false),
            $this->getMock(\Magento\Framework\App\Route\Config::class, [], [], '', false),
            $this->getMock(\Magento\Framework\Locale\ResolverInterface::class),
            $this->getMock(\Magento\Backend\Model\Url::class, [], [], '', false),
            $this->getMock(\Magento\Backend\Model\Auth::class, [], [], '', false),
            $this->_frontResolverMock,
            $this->getMock(\Magento\Framework\Math\Random::class, [], [], '', false),
            $this->getMock(\Magento\Framework\App\RequestInterface::class)
        );
    }

    public function testGetAreaFrontNameLocalConfigCustomFrontName()
    {
        $this->_frontResolverMock->expects(
            $this->once()
        )->method(
            'getFrontName'
        )->will(
            $this->returnValue('custom_backend')
        );

        $this->assertEquals('custom_backend', $this->_helper->getAreaFrontName());
    }

    /**
     * @param array $inputString
     * @param array $expected
     *
     * @dataProvider getPrepareFilterStringValuesDataProvider
     */
    public function testPrepareFilterStringValues(array $inputString, array $expected)
    {
        $inputString = base64_encode(http_build_query($inputString));

        $actual = $this->_helper->prepareFilterString($inputString);

        $this->assertEquals($expected, $actual);
    }

    public function getPrepareFilterStringValuesDataProvider()
    {
        return [
            'both_spaces_value' => [
                ['field' => ' value '],
                ['field' => 'value'],
            ]
        ];
    }
}
