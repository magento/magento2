<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
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
        $this->_frontResolverMock = $this->createMock(\Magento\Backend\App\Area\FrontNameResolver::class);
        $this->_helper = new \Magento\Backend\Helper\Data(
            $this->createMock(\Magento\Framework\App\Helper\Context::class),
            $this->createMock(\Magento\Framework\App\Route\Config::class),
            $this->createMock(\Magento\Framework\Locale\ResolverInterface::class),
            $this->createMock(\Magento\Backend\Model\Url::class),
            $this->createMock(\Magento\Backend\Model\Auth::class),
            $this->_frontResolverMock,
            $this->createMock(\Magento\Framework\Math\Random::class),
            $this->createMock(\Magento\Framework\App\RequestInterface::class)
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
