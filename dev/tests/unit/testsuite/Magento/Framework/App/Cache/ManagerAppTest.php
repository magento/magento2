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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\Cache;

class ManagerAppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheTypeList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendPool;

    protected function setUp()
    {
        $this->cacheTypeList = $this->getMockForAbstractClass('Magento\Framework\App\Cache\TypeListInterface');
        $this->cacheState = $this->getMockForAbstractClass('Magento\Framework\App\Cache\StateInterface');
        $this->response = $this->getMock('Magento\Framework\App\Console\Response', [], [], '', false);
        $this->frontendPool = $this->getMock('Magento\Framework\App\Cache\Type\FrontendPool', [], [], '', false);
    }

    public function testEmptyRequest()
    {
        $this->cacheTypeList->expects($this->once())->method('getTypes')->willReturn(['foo' => '', 'bar' => '']);
        $this->cacheState->expects($this->never())->method('setEnabled');
        $this->cacheState->expects($this->never())->method('persist');
        $this->frontendPool->expects($this->never())->method('get');
        $model = $this->createModel([]);
        $model->launch();
    }

    /**
     * Test setting all cache types to true
     *
     * In this fixture, there are 2 of 3 cache types disabled, but will be enabled
     * so persist() should be invoked once, then clean() for each of those which changed
     */
    public function testSetAllTrue()
    {
        $this->prepareCachesFixture();
        $this->cacheState->expects($this->exactly(2))
            ->method('setEnabled')
            ->will($this->returnValueMap([['bar', true], ['baz', true]]));
        $this->cacheState->expects($this->once())->method('persist');
        // pretend that all cache types use the same frontend
        $frontend = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');
        $this->frontendPool->expects($this->exactly(2))->method('get')->will($this->returnValueMap([
            ['bar', $frontend],
            ['baz', $frontend],
        ]));
        $frontend->expects($this->exactly(2))->method('clean');
        $model = $this->createModel([ManagerApp::KEY_SET => '1']);
        $this->assertSame($this->response, $model->launch());
    }

    /**
     * Test setting all cache types to true
     *
     * Fixture is the same as in previous test, but here the intent to disable all of them.
     * Since only one of them is enabled, the setter should be invoked only once.
     * Also the operation of deactivating cache does not need cleanup (it is relevant when you enable it).
     */
    public function testSetAllFalse()
    {
        $this->prepareCachesFixture();
        $this->cacheState->expects($this->once())->method('setEnabled')->with('foo', false);
        $this->cacheState->expects($this->once())->method('persist');
        $this->frontendPool->expects($this->never())->method('get');
        $model = $this->createModel([ManagerApp::KEY_SET => '0']);
        $model->launch();
    }

    /**
     * Test setting certain cache types to true
     *
     * In the fixture, one of them is currently true and another false
     * So only one of them will be changed (and cleaned up)
     */
    public function testSetTwoTrue()
    {
        $this->prepareCachesFixture(2, [['foo', true], ['baz', false]]);
        $this->cacheState->expects($this->once())->method('setEnabled')->with('baz', true);
        $this->cacheState->expects($this->once())->method('persist');
        $frontend = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');
        $this->frontendPool->expects($this->once())->method('get')->with('baz')->willReturn($frontend);
        $frontend->expects($this->once())->method('clean');
        $model = $this->createModel([ManagerApp::KEY_SET => '1', ManagerApp::KEY_TYPES => 'foo,baz']);
        $model->launch();
    }

    /**
     * A fixture for testing setting enabled/disabled
     *
     * @param int $qty
     * @param array $map
     * @return void
     */
    private function prepareCachesFixture($qty = 3, $map = [['foo', true], ['bar', false], ['baz', false]])
    {
        $typeList = ['foo' => '', 'bar' => '', 'baz' => ''];
        $this->cacheTypeList->expects($this->once())->method('getTypes')->willReturn($typeList);
        $this->cacheState->expects($this->exactly($qty))->method('isEnabled')->will($this->returnValueMap($map));
    }

    /**
     * Test flushing all cache types
     *
     * Emulates situation when some cache frontends reuse the same backend
     * Asserts that the flush is invoked is only once per affected storage
     */
    public function testFlushAll()
    {
        $typeList = ['foo' => '', 'bar' => '', 'baz' => ''];
        $this->cacheTypeList->expects($this->once())->method('getTypes')->willReturn($typeList);
        $frontendFoo = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');
        $frontendBar = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');
        $frontendBaz = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');
        $this->frontendPool->expects($this->exactly(3))->method('get')->will($this->returnValueMap([
            ['foo', $frontendFoo],
            ['bar', $frontendBar],
            ['baz', $frontendBaz],
        ]));
        $backendOne = $this->getMockForAbstractClass('Zend_Cache_Backend_Interface');
        $backendTwo = $this->getMockForAbstractClass('Zend_Cache_Backend_Interface');
        $frontendFoo->expects($this->once())->method('getBackend')->willReturn($backendOne);
        $frontendBar->expects($this->once())->method('getBackend')->willReturn($backendOne);
        $frontendBaz->expects($this->once())->method('getBackend')->willReturn($backendTwo);
        $backendOne->expects($this->once())->method('clean');
        $backendTwo->expects($this->once())->method('clean');
        $model = $this->createModel([ManagerApp::KEY_FLUSH => '']);
        $model->launch();
    }

    public function testGetStatusSummary()
    {
        $types = [
            ['id' => 'foo', 'status' => true],
            ['id' => 'bar', 'status' => false],
        ];
        $this->cacheTypeList->expects($this->once())->method('getTypes')->willReturn($types);
        $model = $this->createModel([]);
        $this->assertSame(['foo' => true, 'bar' => false], $model->getStatusSummary());
    }

    public function testCatchException()
    {
        $model = $this->createModel([]);
        $this->assertFalse($model->catchException(
            $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false),
            new \Exception
        ));
    }

    /**
     * Creates the model with mocked dependencies
     *
     * @param array $request
     * @return ManagerApp
     */
    private function createModel($request)
    {
        return new ManagerApp($this->cacheTypeList, $this->cacheState, $this->response, $this->frontendPool, $request);
    }
}
