<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config;

class WebsiteRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\WebsiteRepository
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\WebsiteFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $websiteFactoryMock;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $websiteCollectionFactoryMock;

    /**
     * @var Config | \PHPUnit\Framework\MockObject\MockObject
     */
    private $appConfigMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->websiteFactoryMock =
            $this->getMockBuilder(\Magento\Store\Model\WebsiteFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->websiteCollectionFactoryMock =
            $this->getMockBuilder(\Magento\Store\Model\ResourceModel\Website\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->model = $objectManager->getObject(
            \Magento\Store\Model\WebsiteRepository::class,
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

    private function initDistroList()
    {
        $repositoryReflection = new \ReflectionClass($this->model);
        $deploymentProperty = $repositoryReflection->getProperty('appConfig');
        $deploymentProperty->setAccessible(true);
        $deploymentProperty->setValue($this->model, $this->appConfigMock);
    }

    public function testGetDefault()
    {
        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
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
        $this->websiteFactoryMock->expects($this->at(0))
            ->method('create')
            ->willReturn($websiteMock);

        $website = $this->model->getDefault();
        $this->assertInstanceOf(\Magento\Store\Api\Data\WebsiteInterface::class, $website);
        $this->assertEquals($websiteMock, $website);
    }

    /**
     */
    public function testGetDefaultIsSeveral()
    {
        $this->expectException(\DomainException::class);

        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
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
     */
    public function testGetDefaultIsZero()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The default website isn\'t defined. Set the website and try again.');

        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
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
