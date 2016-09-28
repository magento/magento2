<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ObjectManager;

use Magento\Framework\Json\JsonInterface;

class ConfigCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigCache
     */
    protected $configCache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheFrontendMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheFrontendMock = $this->getMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->configCache = $objectManagerHelper->getObject(
            \Magento\Framework\App\ObjectManager\ConfigCache::class,
            ['cacheFrontend' => $this->cacheFrontendMock]
        );

        $jsonMock = $this->getMock(JsonInterface::class, [], [], '', false);
        $jsonMock->method('encode')
            ->willReturnCallback(function ($string) {
                return json_encode($string);
            });
        $jsonMock->method('decode')
            ->willReturnCallback(function ($string) {
                return json_decode($string);
            });
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->configCache,
            'json',
            $jsonMock
        );
    }

    protected function tearDown()
    {
        unset($this->configCache);
    }

    public function testGet()
    {
        $key = 'key';
        $this->cacheFrontendMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'diConfig' . $key
        )->will(
            $this->returnValue(false)
        );
        $this->assertEquals(false, $this->configCache->get($key));
    }

    public function testSave()
    {
        $key = 'key';
        $config = ['config'];
        $this->cacheFrontendMock->expects($this->once())->method('save')->with(json_encode($config), 'diConfig' . $key);
        $this->configCache->save($config, $key);
    }
}
