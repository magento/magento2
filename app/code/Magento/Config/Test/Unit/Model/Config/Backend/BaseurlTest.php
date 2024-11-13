<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
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
use Magento\Framework\Validator\Url as UrlValidator;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * Test Class BaseurlTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
            ->onlyMethods(['getOldValue'])
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
     * Test beforeSave method to ensure URL is converted to lower case.
     *
     * @dataProvider beforeSaveDataProvider
     * @param string $value
     * @param string $expectedValue
     * @return void
     */
    public function testBeforeSaveConvertLowerCase(string $value, string $expectedValue): void
    {
        $model = (new ObjectManager($this))->getObject(Baseurl::class);

        $urlValidatorMock = $this->getMockBuilder(UrlValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerInterface = $this->createMock(ObjectManagerInterface::class);
        $objectManagerInterface->expects($this->exactly(1))
            ->method('get')
            ->with(UrlValidator::class)
            ->willReturn($urlValidatorMock);
        AppObjectManager::setInstance($objectManagerInterface);

        $urlValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($expectedValue, ['http', 'https'])
            ->willReturn(true);

        $model->setValue($value);
        $model->beforeSave();
        $this->assertEquals($expectedValue, $model->getValue());
    }

    /**
     * Data provider for testBeforeSaveConvertLowerCase.
     *
     * @return array
     */
    public static function beforeSaveDataProvider(): array
    {
        return [
            ['https://Example1.com/', 'https://example1.com/'],
            ['https://EXAMPLE2.COM/', 'https://example2.com/'],
            ['HTtpS://ExamPLe3.COM/', 'https://example3.com/'],
        ];
    }
}
