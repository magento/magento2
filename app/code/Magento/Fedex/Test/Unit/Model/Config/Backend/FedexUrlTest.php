<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Fedex\Test\Unit\Model\Config\Backend;

use Magento\Fedex\Model\Config\Backend\FedexUrl;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\Url;
use Magento\Rule\Model\ResourceModel\AbstractResource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify behavior of FedexUrl backend type
 */
class FedexUrlTest extends TestCase
{

    /**
     * @var FedexUrl
     */
    private $urlConfig;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->contextMock = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $config = $this->createMock(ScopeConfigInterface::class);
        $cacheTypeList = $this->createMock(TypeListInterface::class);
        $this->url = $this->createMock(Url::class);
        $resource = $this->createMock(AbstractResource::class);
        $resourceCollection = $this->createMock(AbstractDb::class);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $eventManagerMock->expects($this->any())->method('dispatch');
        $this->contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);

        $this->urlConfig = $objectManager->getObject(
            FedexUrl::class,
            [
                'url' => $this->url,
                'context' => $this->contextMock,
                'registry' => $registry,
                'config' => $config,
                'cacheTypeList' => $cacheTypeList,
                'resource' => $resource,
                'resourceCollection' => $resourceCollection,
            ]
        );
    }

    /**
     * @dataProvider validDataProvider
     * @param string|null $data The valid data
     * @throws ValidatorException
     */
    public function testBeforeSave(string $data = null): void
    {
        $this->url->expects($this->any())->method('isValid')->willReturn(true);
        $this->urlConfig->setValue($data);
        $this->urlConfig->beforeSave();
        $this->assertTrue($this->url->isValid($data));
    }

    /**
     * @dataProvider invalidDataProvider
     * @param string $data The invalid data
     */
    public function testBeforeSaveErrors(string $data): void
    {
        $this->url->expects($this->any())->method('isValid')->willReturn(true);
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('Fedex API endpoint URL\'s must use fedex.com');
        $this->urlConfig->setValue($data);
        $this->urlConfig->beforeSave();
    }

    /**
     * Validator Data Provider
     *
     * @return array
     */
    public function validDataProvider(): array
    {
        return [
            [],
            [null],
            [''],
            ['http://fedex.com'],
            ['https://foo.fedex.com'],
            ['http://foo.fedex.com/foo/bar?baz=bash&fizz=buzz'],
        ];
    }

    /**
     * @return \string[][]
     */
    public function invalidDataProvider(): array
    {
        return [
            ['http://fedexfoo.com'],
            ['https://foofedex.com'],
            ['https://fedex.com.fake.com'],
            ['https://fedex.info'],
            ['http://fedex.com.foo.com/foo/bar?baz=bash&fizz=buzz'],
            ['http://foofedex.com/foo/bar?baz=bash&fizz=buzz'],
        ];
    }
}
