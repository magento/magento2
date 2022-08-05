<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\DepersonalizeChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DepersonalizeCheckerTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var Manager|MockObject
     */
    private $moduleManagerMock;

    /**
     * @var Config|MockObject
     */
    private $cacheConfigMock;

    protected function setup(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->cacheConfigMock = $this->createMock(Config::class);
    }

    /**
     * @param array $requestResult
     * @param bool $moduleManagerResult
     * @param bool $cacheConfigResult
     * @param bool $layoutResult
     * @param bool $can Depersonalize
     * @dataProvider checkIfDepersonalizeDataProvider
     */
    public function testCheckIfDepersonalize(
        array $requestResult,
        $moduleManagerResult,
        $cacheConfigResult,
        $layoutResult,
        $canDepersonalize
    ) {
        $this->requestMock->expects($this->any())->method('isAjax')->willReturn($requestResult['ajax']);
        $this->requestMock->expects($this->any())->method('isGet')->willReturn($requestResult['get']);
        $this->requestMock->expects($this->any())->method('isHead')->willReturn($requestResult['head']);
        $this->moduleManagerMock
            ->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($moduleManagerResult);

        $this->cacheConfigMock->expects($this->any())->method('isEnabled')->willReturn($cacheConfigResult);
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class, [], '', false);
        $layoutMock->expects($this->any())->method('isCacheable')->willReturn($layoutResult);

        $object = new DepersonalizeChecker($this->requestMock, $this->moduleManagerMock, $this->cacheConfigMock);
        $this->assertEquals($canDepersonalize, $object->checkIfDepersonalize($layoutMock));
    }

    /**
     * return array
     */
    public function checkIfDepersonalizeDataProvider()
    {
        return [
            [['ajax' => false, 'get' => true, 'head' => false], true, true, true, true],
            [['ajax' => false, 'get' => false, 'head' => true], true, true, true, true],
            [['ajax' => false, 'get' => false, 'head' => false], true, true, true, false],
            [['ajax' => true, 'get' => true, 'head' => false], true, true, true, false],
            [['ajax' => false, 'get' => true, 'head' => false], false, true, true, false],
            [['ajax' => false, 'get' => true, 'head' => false], true, false, true, false],
            [['ajax' => false, 'get' => true, 'head' => false], true, true, false, false],
        ];
    }
}
