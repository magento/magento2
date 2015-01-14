<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Controller\Result;

class BuiltinPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $usePlugin
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getHeaderCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCacheControlHeaderCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCacheDebugHeaderCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getModeCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $processCount
     * @dataProvider dataProvider
     */
    public function testAroundResult(
        $usePlugin, $getHeaderCount, $setCacheControlHeaderCount, $setCacheDebugHeaderCount, $getModeCount,
        $processCount
    ) {
        $cacheControl = 'test';
        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);

        $response->expects($getHeaderCount)->method('getHeader')->with('Cache-Control')
                ->will($this->returnValue(['value' => $cacheControl]));
        $response->expects($setCacheControlHeaderCount)->method('setHeader')
                ->with('X-Magento-Cache-Control', $cacheControl);
        $response->expects($setCacheDebugHeaderCount)->method('setHeader')
                ->with('X-Magento-Cache-Debug', 'MISS', true);

        /** @var \Magento\Framework\Controller\ResultInterface $result */
        $result = $this->getMock('Magento\Framework\Controller\ResultInterface', [], [], '', false);
        $closure = function () use ($result) {
            return $result;
        };

        /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registry->expects($this->once())->method('registry')->with('use_page_cache_plugin')
            ->will($this->returnValue($usePlugin));

        /** @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMock('Magento\PageCache\Model\Config', [], [], '', false);
        $config->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $config->expects($this->once())->method('getType')
            ->will($this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN));

        /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject $state */
        $state = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $state->expects($getModeCount)->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));

        $kernel = $this->getMock('Magento\Framework\App\PageCache\Kernel', [], [], '', false);
        $kernel->expects($processCount)->method('process')->with($response);

        $subject = $this->getMock('Magento\Framework\Controller\ResultInterface', [], [], '', false);

        /** @var \Magento\PageCache\Model\Controller\Result\BuiltinPlugin $plugin */
        $plugin = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\PageCache\Model\Controller\Result\BuiltinPlugin',
            [
                'registry' => $registry,
                'config' => $config,
                'kernel' => $kernel,
                'state' => $state
            ]
        );
        $this->assertSame($result, $plugin->aroundRenderResult($subject, $closure, $response));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [true, $this->once(), $this->at(1), $this->at(2), $this->once(), $this->once()],
            [false, $this->never(), $this->never(), $this->never(), $this->never(), $this->never()]
        ];
    }
}
