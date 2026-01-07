<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Secure;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\MergeService;
use PHPUnit\Framework\TestCase;

class SecureTest extends TestCase
{
    public function testSaveMergedJsCssMustBeCleaned()
    {
        $objectManager = new ObjectManager($this);
        $context = $objectManager->getObject(Context::class);

        $resource = $this->createMock(Data::class);
        $resource->expects($this->any())->method('addCommitCallback')->willReturn($resource);
        $resourceCollection = $this->createMock(AbstractDb::class);
        $mergeService = $this->createMock(MergeService::class);
        $coreRegistry = $this->createMock(Registry::class);
        $coreConfig = $this->createMock(ScopeConfigInterface::class);
        $cacheTypeListMock = $this->createMock(TypeListInterface::class);

        $cacheTypeListMock->expects($this->once())
            ->method('invalidate')
            ->with(Config::TYPE_IDENTIFIER);
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $model = $objectManager->getObject(
            Secure::class,
            [
                'context' => $context,
                'registry' => $coreRegistry,
                'config' => $coreConfig,
                'cacheTypeList' => $cacheTypeListMock,
                'mergeService' => $mergeService,
                'resource' => $resource,
                'resourceCollection' => $resourceCollection,
            ]
        );

        $model->setValue('new_value');
        $model->afterSave();
    }
}
