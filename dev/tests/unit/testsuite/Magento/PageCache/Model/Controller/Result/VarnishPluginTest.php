<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\Model\Controller\Result;

class VarnishPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $usePlugin
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCacheDebugHeaderCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getModeCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $processCount
     * @dataProvider dataProvider
     */
    public function testAroundResult($usePlugin, $setCacheDebugHeaderCount, $getModeCount, $processCount)
    {
        /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $response->expects($setCacheDebugHeaderCount)->method('setHeader')
            ->with('X-Magento-Debug', 1);

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
            ->will($this->returnValue(\Magento\PageCache\Model\Config::VARNISH));

        /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject $state */
        $state = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $state->expects($getModeCount)->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));

        /** @var \Magento\Framework\Controller\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock('Magento\Framework\Controller\ResultInterface', [], [], '', false);

        /** @var \Magento\Framework\App\PageCache\Version|\PHPUnit_Framework_MockObject_MockObject $version */
        $version = $this->getMock('Magento\Framework\App\PageCache\Version', [], [], '', false);
        $version->expects($processCount)->method('process');

        /** @var \Magento\PageCache\Model\Controller\Result\VarnishPlugin $plugin */
        $plugin = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\PageCache\Model\Controller\Result\VarnishPlugin',
            [
                'registry' => $registry,
                'config' => $config,
                'state' => $state,
                'version' => $version
            ]
        );
        $this->assertSame($subject, $plugin->aroundRenderResult($subject, $closure, $response));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [true, $this->once(), $this->once(), $this->once()],
            [false, $this->never(), $this->never(), $this->never()]
        ];
    }
} 