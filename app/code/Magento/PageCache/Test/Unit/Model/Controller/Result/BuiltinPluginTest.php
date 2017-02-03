<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\PageCache\Test\Unit\Model\Controller\Result;

class BuiltinPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Controller\Result\BuiltinPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\Controller\ResultInterface
     */
    protected $subject;

    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $state;

    /**
     * @var \Zend\Http\Header\HeaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $header;

    /**
     * @var \Magento\Framework\App\PageCache\Kernel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernel;

    protected function setUp()
    {
        $result = $this->getMock('Magento\Framework\Controller\ResultInterface', [], [], '', false);
        $this->closure = function() use ($result) {
            return $result;
        };

        $this->header = $this->getMock('Zend\Http\Header\HeaderInterface', [], [], '', false);
        $this->subject = $this->getMock('Magento\Framework\Controller\ResultInterface', [], [], '', false);
        $this->response = $this->getMock(
            'Magento\Framework\App\Response\Http',
            ['getHeader', 'clearHeader', 'setHeader'],
            [],
            '',
            false
        );
        $this->response->expects($this->any())->method('getHeader')->willReturnMap(
            [
                ['X-Magento-Tags', $this->header],
                ['Cache-Control', $this->header]
            ]
        );

        $this->registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);

        $config = $this->getMock('Magento\PageCache\Model\Config', ['isEnabled', 'getType'], [], '', false);
        $config->expects($this->any())->method('isEnabled')->willReturn(true);
        $config->expects($this->any())->method('getType')->willReturn(\Magento\PageCache\Model\Config::BUILT_IN);

        $this->kernel = $this->getMock('Magento\Framework\App\PageCache\Kernel', [], [], '', false);

        $this->state = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->plugin = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\PageCache\Model\Controller\Result\BuiltinPlugin',
            [
                'registry' => $this->registry,
                'config' => $config,
                'kernel' => $this->kernel,
                'state' => $this->state
            ]
        );
    }

    public function testAroundResultWithoutPlugin()
    {
        $this->registry->expects($this->once())->method('registry')->with('use_page_cache_plugin')->willReturn(false);
        $this->kernel->expects($this->never())->method('process')->with($this->response);
        $this->assertSame(
            call_user_func($this->closure),
            $this->plugin->aroundRenderResult($this->subject, $this->closure, $this->response)
        );
    }

    public function testAroundResultWithPlugin()
    {
        $this->registry->expects($this->once())->method('registry')->with('use_page_cache_plugin')->willReturn(true);
        $this->state->expects($this->once())->method('getMode')->willReturn(null);
        $this->header->expects($this->any())->method('getFieldValue')->willReturn('tag,tag');
        $this->response->expects($this->once())->method('clearHeader')->with('X-Magento-Tags');
        $this->response->expects($this->once())->method('setHeader')->with(
            'X-Magento-Tags',
            'tag,' . \Magento\PageCache\Model\Cache\Type::CACHE_TAG
        );
        $this->kernel->expects($this->once())->method('process')->with($this->response);
        $result = call_user_func($this->closure);
        $this->assertSame($result, $this->plugin->aroundRenderResult($this->subject, $this->closure, $this->response));
    }

    public function testAroundResultWithPluginDeveloperMode()
    {
        $this->registry->expects($this->once())->method('registry')->with('use_page_cache_plugin')->willReturn(true);
        $this->state->expects($this->once())->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_DEVELOPER);

        $this->header->expects($this->any())->method('getFieldValue')->willReturnOnConsecutiveCalls('test', 'tag,tag2');

        $this->response->expects($this->any())->method('setHeader')->withConsecutive(
            ['X-Magento-Cache-Control', 'test'],
            ['X-Magento-Cache-Debug', 'MISS', true],
            ['X-Magento-Tags', 'tag,tag2,' . \Magento\PageCache\Model\Cache\Type::CACHE_TAG]
        );

        $this->response->expects($this->once())->method('clearHeader')->with('X-Magento-Tags');
        $this->registry->expects($this->once())->method('registry')->with('use_page_cache_plugin')
            ->will($this->returnValue(true));
        $this->kernel->expects($this->once())->method('process')->with($this->response);

        $result = call_user_func($this->closure);
        $this->assertSame($result, $this->plugin->aroundRenderResult($this->subject, $this->closure, $this->response));
    }
}
