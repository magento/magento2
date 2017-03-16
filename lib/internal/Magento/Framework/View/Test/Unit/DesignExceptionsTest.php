<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DesignExceptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\DesignExceptions */
    private $designExceptions;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfigMock;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    private $requestMock;

    /** @var string */
    private $exceptionConfigPath = 'exception_path';

    /** @var string */
    private $scopeType = 'scope_type';

    /** @var Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->serializerMock = $this->getMock(Json::class, [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->designExceptions = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\DesignExceptions::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'exceptionConfigPath' => $this->exceptionConfigPath,
                'scopeType' => $this->scopeType,
                'serializer' => $this->serializerMock,
            ]
        );
    }

    /**
     * @param string $userAgent
     * @param bool $configValue
     * @param int $callNum
     * @param bool|string $result
     * @param array $expressions
     * @dataProvider getThemeByRequestDataProvider
     */
    public function testGetThemeByRequest($userAgent, $configValue, $callNum, $result, $expressions = [])
    {
        $this->requestMock->expects($this->once())
            ->method('getServer')
            ->with($this->equalTo('HTTP_USER_AGENT'))
            ->will($this->returnValue($userAgent));

        if ($userAgent) {
            $this->scopeConfigMock->expects($this->once())
                ->method('getValue')
                ->with($this->equalTo($this->exceptionConfigPath), $this->equalTo($this->scopeType))
                ->will($this->returnValue($configValue));
        }

        $this->serializerMock->expects($this->exactly($callNum))
            ->method('unserialize')
            ->with($configValue)
            ->willReturn($expressions);

        $this->assertSame($result, $this->designExceptions->getThemeByRequest($this->requestMock));
    }

    /**
     * @return array
     */
    public function getThemeByRequestDataProvider()
    {
        return [
            [false, null, 0, false],
            ['iphone', null, 0, false],
            ['iphone', 'serializedExpressions1', 1, false],
            ['iphone', 'serializedExpressions2', 1, 'matched', [['regexp' => '/iphone/', 'value' => 'matched']]],
            ['explorer', 'serializedExpressions3', 1, false, [['regexp' => '/iphone/', 'value' => 'matched']]],
        ];
    }
}
