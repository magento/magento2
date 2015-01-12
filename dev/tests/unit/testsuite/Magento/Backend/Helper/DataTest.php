<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Helper;

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
            '\Magento\Backend\App\Area\FrontNameResolver',
            [],
            [],
            '',
            false
        );
        $this->_helper = new \Magento\Backend\Helper\Data(
            $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false, false),
            $this->getMock('\Magento\Framework\App\Route\Config', [], [], '', false),
            $this->getMock('Magento\Framework\Locale\ResolverInterface'),
            $this->getMock('\Magento\Backend\Model\Url', [], [], '', false),
            $this->getMock('\Magento\Backend\Model\Auth', [], [], '', false),
            $this->_frontResolverMock,
            $this->getMock('\Magento\Framework\Math\Random', [], [], '', false),
            $this->getMock('\Magento\Framework\App\RequestInterface')
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
