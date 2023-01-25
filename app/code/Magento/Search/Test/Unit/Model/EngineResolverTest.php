<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\EngineResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class EngineResolverTest extends TestCase
{
    /**
     * @var EngineResolver
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var string|MockObject
     */
    private $path;

    /**
     * @var string|MockObject
     */
    private $scopeType;

    /**
     * @var null|string|MockObject
     */
    private $scopeCode;

    /**
     * @var string[]
     */
    private $engines = [];

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var string
     */
    private $defaultEngine = 'defaultentengine';

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->path = 'catalog/search/engine';
        $this->scopeType = 'default';
        $this->scopeCode = null;
        $this->engines = ['defaultentengine', 'anotherengine'];

        $this->model = new EngineResolver(
            $this->scopeConfig,
            $this->engines,
            $this->loggerMock,
            $this->path,
            $this->scopeType,
            $this->scopeCode,
            $this->defaultEngine
        );
    }

    /**
     * Test getCurrentSearchEngine
     */
    public function testGetCurrentSearchEngine()
    {
        $engine = 'anotherengine';

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($engine);

        $this->assertEquals($engine, $this->model->getCurrentSearchEngine());
    }

    /**
     * Test getCurrentSearchEngine
     */
    public function testGetCurrentSearchEngineDefaultEngine()
    {
        $configEngine = 'nonexistentengine';

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($configEngine);

        $this->loggerMock->expects($this->any())
            ->method('error')
            ->with(
                "{$configEngine} search engine doesn't exist. Falling back to {$this->defaultEngine}"
            );

        $this->assertEquals($this->defaultEngine, $this->model->getCurrentSearchEngine());
    }

    /**
     * Test getCurrentSearchEngine
     */
    public function testGetCurrentSearchEngineDefaultEngineNonExistent()
    {
        $configEngine = 'nonexistentengine';
        $this->defaultEngine = 'nonexistenddefaultengine';

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($configEngine);

        $this->loggerMock->expects($this->any())
            ->method('error')
            ->with(
                'Default search engine is not configured, fallback is not possible'
            );

        $model = new EngineResolver(
            $this->scopeConfig,
            $this->engines,
            $this->loggerMock,
            $this->path,
            $this->scopeType,
            $this->scopeCode,
            $this->defaultEngine
        );
        $this->assertEquals($this->defaultEngine, $model->getCurrentSearchEngine());
    }
}
