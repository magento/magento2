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
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\MergeService;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

class BaseurlTest extends TestCase
{
    public function testSaveMergedJsCssMustBeCleaned()
    {
        $context = (new ObjectManager($this))->getObject(Context::class);

        $resource = $this->createMock(Data::class);
        $resource->expects($this->any())->method('addCommitCallback')->willReturn($resource);
        $resourceCollection = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergeService = $this->createMock(MergeService::class);
        $coreRegistry = $this->createMock(Registry::class);
        $coreConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $cacheTypeListMock = $this->getMockBuilder(TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $model = $this->getMockBuilder(Baseurl::class)
            ->setMethods(['getOldValue'])
            ->setConstructorArgs(
                [
                    $context,
                    $coreRegistry,
                    $coreConfig,
                    $cacheTypeListMock,
                    $mergeService,
                    $resource,
                    $resourceCollection
                ]
            )
            ->getMock();

        $cacheTypeListMock->expects($this->once())
            ->method('invalidate')
            ->with(Config::TYPE_IDENTIFIER)
            ->willReturn($model);
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $model->setValue('http://example.com/')->setPath(Store::XML_PATH_UNSECURE_BASE_URL);
        $model->afterSave();
    }

    /**
     * @dataProvider beforeSaveDataProvider
     * @param string $value
     * @param string $expectedValue
     * @return void
     */
    public function testBeforeSaveConvertLowerCase($value, $expectedValue)
    {
        $model = (new ObjectManager($this))->getObject(Baseurl::class);
        $model->setValue($value);
        $model->beforeSave();
        $this->assertEquals($expectedValue, $model->getValue());
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            ['https://Example1.com/', 'https://example1.com/'],
            ['https://EXAMPLE2.COM/', 'https://example2.com/'],
            ['HTtpS://ExamPLe3.COM/', 'https://example3.com/'],
        ];
    }
}
