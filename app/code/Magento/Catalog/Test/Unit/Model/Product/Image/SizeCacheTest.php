<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Image;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product\Image\SizeCache;
use Magento\Framework\App\CacheInterface;

class SizeCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheManager;

    /**
     * @var SizeCache
     */
    protected $model;

    protected function setUp()
    {
        $this->cacheManager = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            SizeCache::class,
            [
                'cacheManager' => $this->cacheManager,
            ]
        );
    }

    /**
     * Test save() method
     */
    public function testSave()
    {
        $width = 100;
        $height = 100;
        $path = '\tmp\img.jpg';
        $this->cacheManager->expects($this->once())
            ->method('save')
            ->with(serialize(['width' => $width, 'height' => $height]), 'IMG_INFO' . $path)
            ->willReturn(true);

        $this->model->save($width, $height, $path);
    }

    /**
     * Test load() method
     */
    public function testLoad()
    {
        $path = '\tmp\img.jpg';
        $this->cacheManager->expects($this->once())
            ->method('load')
            ->with('IMG_INFO' . $path)
            ->willReturn(null);

        $this->model->load($path);
    }
}
