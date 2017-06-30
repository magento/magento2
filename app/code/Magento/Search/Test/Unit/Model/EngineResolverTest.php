<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Search\Model\EngineResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;

class EngineResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\EngineResolver
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var string|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $path;

    /**
     * @var string|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeType;

    /**
     * @var null|string|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeCode;

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

        $this->path = 'catalog/search/engine';
        $this->scopeType = 'default';
        $this->scopeCode = null;

        $this->model = new EngineResolver(
            $this->scopeConfig,
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
        $engine = 'mysql';

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($engine);

        $this->assertEquals($engine, $this->model->getCurrentSearchEngine());
    }
}
