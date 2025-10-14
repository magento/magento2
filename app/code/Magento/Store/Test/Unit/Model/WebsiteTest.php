<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Website\Collection;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    /**
     * @var Website
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var WebsiteFactory|MockObject
     */
    protected $websiteFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var TypeListInterface|MockObject
     */
    private $typeList;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->websiteFactory = $this->getMockBuilder(WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCollection', '__wakeup'])
            ->onlyMethods(['create'])
            ->getMock();

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->typeList = $this->getMockForAbstractClass(TypeListInterface::class);

        /** @var Website $websiteModel */
        $this->model = $this->objectManagerHelper->getObject(
            Website::class,
            [
                'websiteFactory' => $this->websiteFactory,
                'storeManager' => $this->storeManager,
                'typeList' => $this->typeList
            ]
        );
    }

    public function testIsCanDelete()
    {
        $websiteCollection = $this->createPartialMock(
            Collection::class,
            ['getSize']
        );
        $websiteCollection->expects($this->any())->method('getSize')->willReturn(2);

        $this->websiteFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->websiteFactory);
        $this->websiteFactory->expects($this->any())
            ->method('getCollection')
            ->willReturn($websiteCollection);

        $this->model->setId(2);
        $this->assertTrue($this->model->isCanDelete());
    }

    public function testGetScopeType()
    {
        $this->assertEquals(ScopeInterface::SCOPE_WEBSITE, $this->model->getScopeType());
    }

    public function testGetScopeTypeName()
    {
        $this->assertEquals('Website', $this->model->getScopeTypeName());
    }

    public function testGetCacheTags()
    {
        $this->assertEquals([Website::CACHE_TAG], $this->model->getCacheTags());
    }

    public function testAfterSaveNewObject()
    {
        $this->storeManager->expects($this->once())
            ->method('reinitStores');

        $this->model->afterSave();
    }

    public function testAfterSaveObject()
    {
        $this->model->setId(1);

        $this->storeManager->expects($this->never())
            ->method('reinitStores');

        $this->typeList->expects($this->once())
            ->method('invalidate')
            ->with(['full_page', Config::TYPE_IDENTIFIER]);

        $this->model->afterSave();
    }

    public function testAfterDelete()
    {
        $this->typeList->expects($this->exactly(2))
            ->method('cleanType')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == 'full_page' || $arg == Config::TYPE_IDENTIFIER) {
                        return null;
                    }
                }
            );

        $this->model->afterDelete();
    }
}
