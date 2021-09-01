<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use DomainException;
use Magento\Framework\App\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\WebsiteRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class WebsiteRepositoryTest extends TestCase
{
    /**
     * @var WebsiteRepository
     */
    protected $model;

    /**
     * @var WebsiteFactory|MockObject
     */
    protected $websiteFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $websiteCollectionFactoryMock;

    /**
     * @var Config|MockObject
     */
    private $appConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->websiteFactoryMock =
            $this->getMockBuilder(WebsiteFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock();
        $this->websiteCollectionFactoryMock =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock();
        $this->model = $objectManager->getObject(
            WebsiteRepository::class,
            [
                'factory' => $this->websiteFactoryMock,
                'websiteCollectionFactory' => $this->websiteCollectionFactoryMock
            ]
        );
        $this->appConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->initDistroList();
    }

    /**
     * @return void
     */
    private function initDistroList(): void
    {
        $repositoryReflection = new ReflectionClass($this->model);
        $deploymentProperty = $repositoryReflection->getProperty('appConfig');
        $deploymentProperty->setAccessible(true);
        $deploymentProperty->setValue($this->model, $this->appConfigMock);
    }

    /**
     * @return void
     */
    public function testGetDefault(): void
    {
        $websiteMock = $this->getMockBuilder(WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMockForAbstractClass();
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->with('scopes', 'websites')
            ->willReturn([
                'some_code' => [
                    'code' => 'some_code',
                    'is_default' => 1
                ],
                'some_code_2' => [
                    'code' => 'some_code_2',
                    'is_default' => 0
                ]
            ]);
        $this->websiteFactoryMock
            ->method('create')
            ->willReturn($websiteMock);

        $website = $this->model->getDefault();
        $this->assertInstanceOf(WebsiteInterface::class, $website);
        $this->assertEquals($websiteMock, $website);
    }

    /**
     * @return void
     */
    public function testGetDefaultIsSeveral(): void
    {
        $this->expectException(DomainException::class);
        $websiteMock = $this->getMockBuilder(WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMockForAbstractClass();
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->with('scopes', 'websites')
            ->willReturn([
                'some_code' => [
                    'code' => 'some_code',
                    'is_default' => 1
                ],
                'some_code_2' => [
                    'code' => 'some_code_2',
                    'is_default' => 1
                ]
            ]);
        $this->websiteFactoryMock->expects($this->any())->method('create')->willReturn($websiteMock);

        $this->model->getDefault();

        $this->expectExceptionMessage(
            "The default website is invalid. Make sure no more than one default is defined and try again."
        );
    }

    /**
     * @return void
     */
    public function testGetDefaultIsZero(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The default website isn\'t defined. Set the website and try again.');
        $websiteMock = $this->getMockBuilder(WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMockForAbstractClass();
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->with('scopes', 'websites')
            ->willReturn([
                'some_code' => [
                    'code' => 'some_code',
                    'is_default' => 0
                ],
                'some_code_2' => [
                    'code' => 'some_code_2',
                    'is_default' => 0
                ]
            ]);
        $this->websiteFactoryMock->expects($this->any())->method('create')->willReturn($websiteMock);

        $this->model->getDefault();
    }
}
