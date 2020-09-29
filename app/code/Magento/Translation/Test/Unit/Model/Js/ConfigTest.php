<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Js;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Translation\Model\Js\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $scopeMock;

    /**
     * @var string
     */
    protected $patterns = ['test_pattern'];

    protected function setUp(): void
    {
        $this->scopeMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeMock,
                'patterns' => $this->patterns
            ]
        );
    }

    public function testIsEmbeddedStrategy()
    {
        $this->scopeMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_STRATEGY)
            ->willReturn(Config::EMBEDDED_STRATEGY);
        $this->assertTrue($this->model->isEmbeddedStrategy());
    }

    public function testDictionaryEnabled()
    {
        $this->scopeMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_STRATEGY)
            ->willReturn(Config::DICTIONARY_STRATEGY);
        $this->assertTrue($this->model->dictionaryEnabled());
    }

    public function testGetPatterns()
    {
        $this->assertEquals($this->patterns, $this->model->getPatterns());
    }
}
