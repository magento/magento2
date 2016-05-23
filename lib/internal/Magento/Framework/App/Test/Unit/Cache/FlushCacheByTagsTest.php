<?php
/**
 * Created by PhpStorm.
 * User: akasian
 * Date: 1/25/16
 * Time: 10:26 AM
 */

namespace Magento\Framework\App\Test\Unit\Cache;

class FlushCacheByTagsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Type\FrontendPool
     */
    private $frontendPool;

    /**
     * @var \Magento\Framework\App\Cache\FlushCacheByTags
     */
    private $plugin;

    protected function setUp()
    {
        $this->cacheState = $this->getMockForAbstractClass(\Magento\Framework\App\Cache\StateInterface::class);
        $this->frontendPool = $this->getMock(\Magento\Framework\App\Cache\Type\FrontendPool::class, [], [], '', false);
        $this->plugin = new \Magento\Framework\App\Cache\FlushCacheByTags(
            $this->frontendPool,
            $this->cacheState,
            ['test']
        );

    }

    public function testAroundSave()
    {
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->plugin->aroundSave(
            $resource,
            function () use ($resource) {
                return $resource;
            },
            $model
        );
        $this->assertSame($resource, $result);
    }

    public function testAroundDelete()
    {
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->plugin->aroundDelete(
            $resource,
            function () use ($resource) {
                return $resource;
            },
            $model
        );
        $this->assertSame($resource, $result);
    }

    public function testAroundSaveWithInterface()
    {
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)

            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->plugin->aroundSave(
            $resource,
            function () use ($resource) {
                return $resource;
            },
            $model
        );
        $this->assertSame($resource, $result);
    }
}
