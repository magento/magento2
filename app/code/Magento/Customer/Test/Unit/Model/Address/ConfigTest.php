<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Address\Config;
use Magento\Customer\Model\Address\Config\Reader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $addressHelperMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Config
     */
    protected $model;

    protected function setUp(): void
    {
        $cacheId = 'cache_id';
        $objectManagerHelper = new ObjectManager($this);
        $this->storeMock = $this->createMock(Store::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $readerMock = $this->createMock(Reader::class);
        $cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $storeManagerMock = $this->createMock(StoreManager::class);
        $storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->willReturn(
            $this->storeMock
        );

        $this->addressHelperMock = $this->createMock(Address::class);

        $cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $cacheId
        )->willReturn(
            false
        );

        $fixtureConfigData = require __DIR__ . '/Config/_files/formats_merged.php';

        $readerMock->expects($this->once())->method('read')->willReturn($fixtureConfigData);

        $cacheMock->expects($this->once())
            ->method('save')
            ->with(
                json_encode($fixtureConfigData),
                $cacheId
            );

        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->method('serialize')
            ->willReturn(json_encode($fixtureConfigData));
        $serializerMock->method('unserialize')
            ->willReturn($fixtureConfigData);

        $this->model = $objectManagerHelper->getObject(
            Config::class,
            [
                'reader' => $readerMock,
                'cache' => $cacheMock,
                'storeManager' => $storeManagerMock,
                'scopeConfig' => $this->scopeConfigMock,
                'cacheId' => $cacheId,
                'serializer' => $serializerMock,
                'addressHelper' => $this->addressHelperMock,
            ]
        );
    }

    public function testGetStore()
    {
        $this->assertEquals($this->storeMock, $this->model->getStore());
    }

    public function testSetStore()
    {
        $this->model->setStore($this->storeMock);
        $this->assertEquals($this->storeMock, $this->model->getStore());
    }

    public function testGetFormats()
    {
        $this->storeMock->expects($this->once())->method('getId');

        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn('someValue');

        $rendererMock = $this->createMock(DataObject::class);

        $this->addressHelperMock->expects(
            $this->any()
        )->method(
            'getRenderer'
        )->willReturn(
            $rendererMock
        );

        $firstExpected = new DataObject();
        $firstExpected->setCode(
            'format_one'
        )->setTitle(
            'format_one_title'
        )->setDefaultFormat(
            'someValue'
        )->setEscapeHtml(
            false
        )->setRenderer(
            null
        );

        $secondExpected = new DataObject();
        $secondExpected->setCode(
            'format_two'
        )->setTitle(
            'format_two_title'
        )->setDefaultFormat(
            'someValue'
        )->setEscapeHtml(
            true
        )->setRenderer(
            null
        );
        $expectedResult = [$firstExpected, $secondExpected];

        $this->assertEquals($expectedResult, $this->model->getFormats());
    }
}
