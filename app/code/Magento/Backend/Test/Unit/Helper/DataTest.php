<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Helper;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Url;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Route\Config;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Math\Random;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var MockObject
     */
    protected $_frontResolverMock;

    protected function setUp(): void
    {
        $this->_frontResolverMock = $this->createMock(FrontNameResolver::class);
        $this->_helper = new Data(
            $this->createMock(Context::class),
            $this->createMock(Config::class),
            $this->getMockForAbstractClass(ResolverInterface::class),
            $this->createMock(Url::class),
            $this->createMock(Auth::class),
            $this->_frontResolverMock,
            $this->createMock(Random::class),
            $this->getMockForAbstractClass(RequestInterface::class)
        );
    }

    public function testGetAreaFrontNameLocalConfigCustomFrontName()
    {
        $this->_frontResolverMock->expects(
            $this->once()
        )->method(
            'getFrontName'
        )->willReturn(
            'custom_backend'
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

    /**
     * @return array
     */
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
