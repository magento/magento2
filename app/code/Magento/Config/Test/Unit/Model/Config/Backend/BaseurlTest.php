<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Baseurl;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\MergeService;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseurlTest extends TestCase
{
    /**
     * @var Baseurl
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var TypeListInterface|MockObject
     */
    private $cacheTypeListMock;

    /**
     * @var MergeService|MockObject
     */
    private $mergeServiceMock;

    /**
     * @var Data|MockObject
     */
    private $resourceMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->cacheTypeListMock = $this->getMockBuilder(TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergeServiceMock = $this->createMock(MergeService::class);
        $this->resourceMock = $this->createMock(Data::class);

        $this->model = $objectManager->getObject(
            Baseurl::class,
            [
                'config' => $this->scopeConfigMock,
                'cacheTypeList' => $this->cacheTypeListMock,
                'mergeService' => $this->mergeServiceMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    public function testSaveMergedJsCssMustBeCleaned()
    {
        $this->resourceMock->method('addCommitCallback')->willReturn($this->resourceMock);
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->with(Store::XML_PATH_UNSECURE_BASE_URL, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0)
            ->willReturn('http://example-old.com');
        $this->mergeServiceMock->expects($this->once())->method('cleanMergedJsCss');
        $this->cacheTypeListMock->expects($this->once())
            ->method('invalidate')
            ->with(Config::TYPE_IDENTIFIER)
            ->willReturn($this->model);

        $this->model->setValue('http://example.com/')
            ->setPath(Store::XML_PATH_UNSECURE_BASE_URL)
            ->setScopeCode(0);
        $this->model->afterSave();
    }
}
