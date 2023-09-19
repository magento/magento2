<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Validation\StoreValidator;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    private Group $model;

    private Context $context;

    private Registry $registry;

    private ExtensionAttributesFactory $extensionFactory;

    private AttributeValueFactory $customAttributeFactory;

    private Data $configDataResource;

    private CollectionFactory $storeListFactory;

    private StoreManagerInterface $storeManager;

    private AbstractResource $resource;

    private AbstractDb $resourceCollection;

    private ManagerInterface $eventManager;

    private PoisonPillPutInterface $pillPut;

    private StoreValidator $modelValidator;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionFactory = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customAttributeFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configDataResource = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeListFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->resource = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(['getIdFieldName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceCollection = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->pillPut = $this->getMockForAbstractClass(PoisonPillPutInterface::class);

        $this->modelValidator = $this->getMockBuilder(StoreValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Group(
            $this->context,
            $this->registry,
            $this->extensionFactory,
            $this->customAttributeFactory,
            $this->configDataResource,
            $this->storeListFactory,
            $this->storeManager,
            $this->resource,
            $this->resourceCollection,
            [],
            $this->eventManager,
            $this->pillPut,
            $this->modelValidator
        );
    }

    public function testGetCacheTags()
    {
        $this->assertEquals([Group::CACHE_TAG], $this->model->getCacheTags());
    }
}
