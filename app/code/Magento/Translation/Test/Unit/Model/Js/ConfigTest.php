<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Js;

use Magento\Translation\Model\Js\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeMock;

    /**
     * @var string
     */
    protected $patterns = ['test_pattern'];

    protected function setUp()
    {
        $this->scopeMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Translation\Model\Js\Config::class,
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
