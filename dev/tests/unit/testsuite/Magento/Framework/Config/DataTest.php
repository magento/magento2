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