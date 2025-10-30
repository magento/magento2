<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\DesignExceptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DesignExceptionsTest extends TestCase
{
    /** @var DesignExceptions */
    private $designExceptions;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var ScopeConfigInterface|MockObject */
    private $scopeConfigMock;

    /** @var Http|MockObject */
    private $requestMock;

    /** @var string */
    private $exceptionConfigPath = 'exception_path';

    /** @var string */
    private $scopeType = 'scope_type';

    /** @var Json|MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->serializerMock = $this->createMock(Json::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->designExceptions = $this->objectManagerHelper->getObject(
            DesignExceptions::class,
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
            ->with('HTTP_USER_AGENT')
            ->willReturn($userAgent);

        if ($userAgent) {
            $this->scopeConfigMock->expects($this->once())
                ->method('getValue')
                ->with($this->exceptionConfigPath, $this->scopeType)
                ->willReturn($configValue);
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
    public static function getThemeByRequestDataProvider()
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
