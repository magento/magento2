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

namespace Magento\Css\PreProcessor\Cache;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Css\PreProcessor\Cache\Plugin */
    protected $plugin;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Css\PreProcessor\Cache\CacheManagerFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheManagerFactoryMock;

    /** @var \Magento\Css\PreProcessor\Cache\CacheManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheManager;

    /** @var \Magento\Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $targetDirMock;

    protected function setUp()
    {
        $this->targetDirMock = $this->getMock('Magento\Filesystem\Directory\WriteInterface', [], [], '', false);
        $this->cacheManagerFactoryMock = $this->getMock(
            'Magento\Css\PreProcessor\Cache\CacheManagerFactory',
            [],
            [],
            '',
            false
        );
        $this->cacheManager = $this->getMock('Magento\Css\PreProcessor\Cache\CacheManager', [], [], '', false);
        $this->cacheManagerFactoryMock->expects($this->any())
            ->method('create')
            ->with($this->anything(), $this->anything())
            ->will($this->returnValue($this->cacheManager));
        $this->loggerMock = $this->getMock('Magento\Logger', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Cache\Plugin',
            [
                'cacheManagerFactory' => $this->cacheManagerFactoryMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @param array $cacheManagerData
     * @param string|null $expected
     * @dataProvider aroundProcessDataProvider
     */
    public function testAroundProcess($arguments, $invocationChain, $cacheManagerData, $expected)
    {
        if (!empty($cacheManagerData)) {
            foreach ($cacheManagerData as $method => $info) {
                if ($method === 'getCachedFile') {
                    $this->cacheManager->expects($this->once())
                        ->method($method)
                        ->will($this->returnValue($info['result']));
                } else {
                    $this->cacheManager->expects($this->once())
                        ->method($method)
                        ->with($this->equalTo($info['with']))
                        ->will($this->returnValue($info['result']));
                }

            }
        }
        $this->assertEquals($expected, $this->plugin->aroundProcess($arguments, $invocationChain));
    }

    /**
     * @return array
     */
    public function aroundProcessDataProvider()
    {
        $argFirst = [
            'css\style.less', // lessFilePath
            [], // params
            $this->targetDirMock, // target directory
            'css\style.css' // sourceFile
        ];
        $expectedFirst = 'expectedFirst';
        $invChainFirst = $this->getMock('Magento\Code\Plugin\InvocationChain', [], [], '', false);
        $invChainFirst->expects($this->once())
            ->method('proceed')
            ->with($this->equalTo($argFirst))
            ->will($this->returnValue($expectedFirst));

        $invChainSecond = $this->getMock('Magento\Code\Plugin\InvocationChain', [], [], '', false);
        $argSecond = [
            'css\style.less', // lessFilePath
            [], // params
            $this->targetDirMock, // target directory
            null // sourceFile
        ];

        $expectedThird = 'expectedThird';
        $argThird = [
            'css\style.less', // lessFilePath
            [], // params
            $this->targetDirMock, // target directory
            null // sourceFile
        ];

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
                'cacheManagerData' => ['getCachedFile' => ['result' => 'cached-value']],
                'expected' => 'cached-value'
            ],
            'cached value does not exist' => [
                'arguments' => $argThird,
                'invocationChain' => $invChainThird,
                'cacheManagerData' => [
                    'getCachedFile' => ['result' => null],
                    'saveCache' => ['with' => $expectedThird, 'result' => 'self']
                ],
                'expected' => $expectedThird
            ],
        ];
    }

    public function testAroundProcessException()
    {
        $arguments = [
            'css\style.less', // lessFilePath
            [], // params
            $this->targetDirMock, // target directory
            null // sourceFile
        ];

        $this->cacheManager->expects($this->once())
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


    public function testBeforeProcessLessInstructions()
    {
        $arguments = ['some\less\filePth.less', ['some', 'kind', 'of' ,'params']];
        list($lessFilePath, $params) = $arguments;

        $method = new \ReflectionMethod('Magento\Css\PreProcessor\Cache\Plugin', 'initializeCacheManager');
        $method->setAccessible(true);
        $this->assertEquals($this->plugin, $method->invoke($this->plugin, $lessFilePath, $params));

        $this->cacheManager->expects($this->once())
            ->method('addEntityToCache')
            ->with($this->equalTo($lessFilePath), $this->equalTo($params))
            ->will($this->returnSelf());

        $this->assertEquals($arguments, $this->plugin->beforeProcessLessInstructions($arguments));
    }
}
