<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Search\Model\EngineResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class EngineResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Search\Model\EngineResolver
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var string|\PHPUnit_Framework_MockObject_MockObject
     */
    private $path;

    /**
     * @var string|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeType;

    /**
     * @var null|string|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeCode;

    /**
     * @var string[]
     */
    private $engines = [];

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->path = 'catalog/search/engine';
        $this->scopeType = 'default';
        $this->scopeCode = null;
        $this->engines = [EngineResolver::CATALOG_SEARCH_MYSQL_ENGINE, 'anotherengine'];

        $this->model = new EngineResolver(
            $this->scopeConfig,
            $this->engines,
            $this->loggerMock,
            $this->path,
            $this->scopeType,
            $this->scopeCode
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
    public function testGetCurrentSearchEngineWithoutEngine()
    {
        $engine = 'nonexistentengine';

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($engine);

        $this->loggerMock->expects($this->any())
            ->method('error')
            ->with(
                $engine . ' search engine doesn\'t exists. Falling back to '
                . EngineResolver::CATALOG_SEARCH_MYSQL_ENGINE
            );

        $this->assertEquals(EngineResolver::CATALOG_SEARCH_MYSQL_ENGINE, $this->model->getCurrentSearchEngine());
    }
}
