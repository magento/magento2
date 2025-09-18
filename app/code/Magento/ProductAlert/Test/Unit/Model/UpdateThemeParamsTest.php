<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\DesignInterface;
use Magento\ProductAlert\Block\Email\Stock;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\ProductAlert\Model\UpdateThemeParams;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class UpdateThemeParamsTest to check valid
 * template file returns for multi store setup
 */
class UpdateThemeParamsTest extends TestCase
{
    /**
     * @var UpdateThemeParams
     */
    private $model;

    /**
     * @var DesignInterface|MockObject
     */
    private $designMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Stock|MockObject
     */
    private $stockMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->designMock = $this->createMock(DesignInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->stockMock = $this->createMock(Stock::class);

        $this->model = new UpdateThemeParams(
            $this->designMock,
            $this->storeManagerMock,
            $this->stockMock
        );
    }

    /**
     * Test cases to check template file returns for multi store setup
     *
     * @param string $templateFileName
     * @param string $stockTemplateFileName
     * @param int $storeId
     * @param array $params
     * @return void
     * @throws Exception
     * @throws NoSuchEntityException
     * @dataProvider getTemplateFileDataProvider
     */
    public function testBeforeGetTemplateFileName(
        string $templateFileName,
        string $stockTemplateFileName,
        int $storeId,
        array $params
    ): void {
        $resolverMock = $this->createMock(Resolver::class);
        $storeMock = $this->createMock(Store::class);
        $this->stockMock->method('getTemplate')->willReturn($stockTemplateFileName);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn($storeId);
        $this->designMock
                ->method('getConfigurationDesignTheme')
                ->willReturn($params['themeId']);

        $expectedArr = $this->model->beforeGetTemplateFileName($resolverMock, $templateFileName, $params);
        $this->assertEquals([$templateFileName, $params], $expectedArr);
    }

    /**
     * Data provider for testBeforeGetTemplateFileName
     *
     * @return array
     */
    public static function getTemplateFileDataProvider(): array
    {
        return [
            'test cases with valid template file name' => [
                'Magento_ProductAlert::email/stock.phtml',
                'Magento_ProductAlert::email/stock.phtml',
                1,
                ['themeId' => 1]
            ],
            'test cases with invalid template file name' => [
                'test.phtml',
                'Magento_ProductAlert::email/stock.phtml',
                1,
                ['themeId' => 1]
            ]
        ];
    }
}
