<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Config\Type;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Store\App\Config\Type\Scopes;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;

class ScopesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var Scopes
     */
    private $configType;

    public function setUp()
    {
        $this->source = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->cache = $this->getMockBuilder(FrontendInterface::class)
            ->getMockForAbstractClass();

        $this->configType = new Scopes($this->source, $this->cache);
    }

    /**
     * @param bool $isCached
     * @dataProvider getDataProvider
     */
    public function testGet($isCached)
    {
        $storeCode = 'myStore';
        $storeData = [
            'name' => 'My store'
        ];
        $data = [
            'stores' => [
                $storeCode => $storeData
            ]
        ];
        $this->cache->expects($this->once())
            ->method('load')
            ->with(Scopes::CONFIG_TYPE)
            ->willReturn($isCached ? serialize(new DataObject($data)) : false);

        if (!$isCached) {
            $this->source->expects($this->once())
                ->method('get')
                ->with('')
                ->willReturn($data);
            $this->cache->expects($this->once())
                ->method('save')
                ->with(
                    serialize(new DataObject($data)),
                    Scopes::CONFIG_TYPE,
                    [Group::CACHE_TAG, Store::CACHE_TAG, Website::CACHE_TAG]
                );
        }

        $this->assertEquals($storeData, $this->configType->get('stores/' . $storeCode));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
