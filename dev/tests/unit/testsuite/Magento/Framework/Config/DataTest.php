<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Config\ReaderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $reader;
    /** @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cache;
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManagerHelper;

    public function setUp()
    {
        $this->reader = $this->getMockBuilder('Magento\\Framework\\Config\\ReaderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->getMockBuilder('Magento\\Framework\\Config\\CacheInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGet()
    {
        $data = ['a' => 'b'];
        $cacheid = 'test';
        $this->cache->expects($this->once())->method('load')->will($this->returnValue(false));
        $this->reader->expects($this->once())->method('read')->will($this->returnValue($data));

        $config = new \Magento\Framework\Config\Data(
            $this->reader, $this->cache, $cacheid
        );
        $this->assertEquals($data, $config->get());
        $this->assertEquals('b', $config->get('a'));
        $this->assertEquals(null, $config->get('a/b'));
        $this->assertEquals(33, $config->get('a/b', 33));
    }

    public function testReset()
    {
        $cacheid = 'test';
        $this->cache->expects($this->once())->method('load')->will($this->returnValue(serialize([])));
        $this->cache->expects($this->once())->method('remove')->with($cacheid);

        $config = new \Magento\Framework\Config\Data(
            $this->reader,
            $this->cache,
            $cacheid
        );

        $config->reset();
    }
}
