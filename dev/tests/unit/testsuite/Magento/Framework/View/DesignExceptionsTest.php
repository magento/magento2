<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class DesignExceptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\DesignExceptions */
    protected $designExceptions;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var string */
    protected $exceptionConfigPath = 'exception_path';

    /** @var string */
    protected $scopeType = 'scope_type';

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->designExceptions = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\DesignExceptions',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'exceptionConfigPath' => $this->exceptionConfigPath,
                'scopeType' => $this->scopeType
            ]
        );
    }

    /**
     * @param string $userAgent
     * @param bool $useConfig
     * @param bool|string $result
     * @param array $expressions
     * @dataProvider getThemeByRequestDataProvider
     */
    public function testGetThemeByRequest($userAgent, $useConfig, $result, $expressions = [])
    {
        $this->requestMock->expects($this->once())
            ->method('getServer')
            ->with($this->equalTo('HTTP_USER_AGENT'))
            ->will($this->returnValue($userAgent));

        if ($useConfig) {
            $this->scopeConfigMock->expects($this->once())
                ->method('getValue')
                ->with($this->equalTo($this->exceptionConfigPath), $this->equalTo($this->scopeType))
                ->will($this->returnValue(serialize($expressions)));
        }

        $this->assertSame($result, $this->designExceptions->getThemeByRequest($this->requestMock));
    }

    /**
     * @return array
     */
    public function getThemeByRequestDataProvider()
    {
        return [
            [false, false, false],
            ['iphone', false, false],
            ['iphone', true, false],
            ['iphone', true, 'matched', [['regexp' => '/iphone/', 'value' => 'matched']]],
            ['explorer', true, false, [['regexp' => '/iphone/', 'value' => 'matched']]],
        ];
    }
}
