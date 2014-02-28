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

namespace Magento\Css\PreProcessor\Cache\Plugin;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class LessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Css\PreProcessor\Cache\Plugin\Less
     */
    protected $plugin;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Css\PreProcessor\Cache\CacheManager
     */
    protected $cacheManagerMock;

    /**
     * @var \Magento\Logger
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cacheManagerMock = $this->getMock('Magento\Css\PreProcessor\Cache\CacheManager', [], [], '', false);
        $this->loggerMock = $this->getMock('Magento\Logger', [], [], '', false);
        $this->plugin = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Cache\Plugin\Less',
            [
                'cacheManager' => $this->cacheManagerMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @param array $cacheManagerData
     * @dataProvider aroundProcessDataProvider
     */
    public function testAroundProcess($arguments, $invocationChain, $cacheManagerData)
    {
        if (!empty($cacheManagerData)) {
            foreach ($cacheManagerData as $method => $info) {
                if ($method === 'getCachedFile') {
                    $this->cacheManagerMock->expects($this->once())
                        ->method($method)
                        ->will($this->returnValue($info['result']));
                } else {
                    $this->cacheManagerMock->expects($this->once())
                        ->method($method)
                        ->will($this->returnValue($info['result']));
                }

            }
        }
        $this->assertInstanceOf(
            'Magento\View\Publisher\CssFile',
            $this->plugin->aroundProcess($arguments, $invocationChain)
        );
    }

    /**
     * @return array
     */
    public function aroundProcessDataProvider()
    {
        /**
         * Prepare first item
         */
        $cssFileFirst = $this->getMock('Magento\View\Publisher\CssFile', [], [], '', false);
        $cssFileFirst->expects($this->once())
            ->method('getSourcePath')
            ->will($this->returnValue(false));

        $argFirst[] = $cssFileFirst;

        $expectedFirst = $this->getMock('Magento\View\Publisher\CssFile', [], [], '', false);
        $cssFileFirst->expects($this->once())
            ->method('buildUniquePath')
            ->will($this->returnValue('expectedFirst'));

        $invChainFirst = $this->getMock('Magento\Code\Plugin\InvocationChain', [], [], '', false);
        $invChainFirst->expects($this->once())
            ->method('proceed')
            ->with($this->equalTo($argFirst))
            ->will($this->returnValue($expectedFirst));

        /**
         * Prepare second item
         */
        $cssFileSecond = $this->getMock('Magento\View\Publisher\CssFile', [], [], '', false);
        $cssFileSecond->expects($this->once())
            ->method('getSourcePath')
            ->will($this->returnValue(false));

        $argSecond[] = $cssFileSecond;
        $invChainSecond = $this->getMock('Magento\Code\Plugin\InvocationChain', [], [], '', false);

        /**
         * Prepare third item
         */
        $cssFileThird = $this->getMock('Magento\View\Publisher\CssFile', [], [], '', false);
        $cssFileThird->expects($this->once())
            ->method('getSourcePath')
            ->will($this->returnValue(false));

        $argThird[] = $cssFileThird;

        $expectedThird = $this->getMock('Magento\View\Publisher\CssFile', [], [], '', false);

        $invChainThird = $this->getMock('Magento\Code\Plugin\InvocationChain', [], [], '', false);
        $invChainThird->expects($this->once())
            ->method('proceed')
            ->with($this->equalTo($argThird))
            ->will($this->returnValue($expectedThird));

        return [
            'source path already exist' => [
                'arguments' => $argFirst,
                'invocationChain' => $invChainFirst,
                'cacheManagerData' => [],
                'expected' => $expectedFirst
            ],
            'cached value exists' => [
                'arguments' => $argSecond,
                'invocationChain' => $invChainSecond,
                'cacheManagerData' => ['getCachedFile' => ['result' => $cssFileSecond]],
                'expected' => 'cached-value'
            ],
            'cached value does not exist' => [
                'arguments' => $argThird,
                'invocationChain' => $invChainThird,
                'cacheManagerData' => [
                    'getCachedFile' => ['result' => null],
                    'saveCache' => ['result' => 'self']
                ],
                'expected' => $expectedThird
            ],
        ];
    }

    public function testAroundProcessException()
    {
        $cssFile = $this->getMock('Magento\View\Publisher\CssFile', [], [], '', false);
        $cssFile->expects($this->once())
            ->method('getSourcePath')
            ->will($this->returnValue(false));

        $arguments[] = $cssFile;

        $this->cacheManagerMock->expects($this->once())
            ->method('getCachedFile')
            ->will($this->returnValue(null));

        $exception = new \Magento\Filesystem\FilesystemException('Test Message');
        $invocationChain = $this->getMock('Magento\Code\Plugin\InvocationChain', [], [], '', false);
        $invocationChain->expects($this->once())
            ->method('proceed')
            ->with($this->equalTo($arguments))
            ->will($this->throwException($exception));
        $this->loggerMock->expects($this->once())
            ->method('logException')
            ->with($this->equalTo($exception))
            ->will($this->returnSelf());
        $this->assertNull($this->plugin->aroundProcess($arguments, $invocationChain));
    }
}
