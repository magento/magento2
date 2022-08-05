<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Json;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\DesignInterface;
use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\DataProvider;
use Magento\Translation\Model\Json\PreProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PreProcessorTest extends TestCase
{
    /**
     * @var PreProcessor
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var DataProvider|MockObject
     */
    private $dataProviderMock;

    /**
     * @var AreaList|MockObject
     */
    private $areaListMock;

    /**
     * @var TranslateInterface|MockObject
     */
    private $translateMock;

    /**
     * @var DesignInterface|MockObject
     */
    private $designMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->dataProviderMock = $this->createMock(DataProvider::class);
        $this->areaListMock = $this->createMock(AreaList::class);
        $this->translateMock = $this->getMockForAbstractClass(TranslateInterface::class);
        $this->designMock = $this->getMockForAbstractClass(DesignInterface::class);
        $this->model = new PreProcessor(
            $this->configMock,
            $this->dataProviderMock,
            $this->areaListMock,
            $this->translateMock,
            $this->designMock
        );
    }

    /**
     * Test 'process' method.
     *
     * @param array $data
     * @param array $expects
     * @dataProvider processDataProvider
     */
    public function testProcess(array $data, array $expects)
    {
        $chain = $this->createMock(Chain::class);
        $asset = $this->createMock(File::class);
        $context = $this->createMock(FallbackContext::class);
        $fileName = 'js-translation.json';
        $targetPath = 'path/js-translation.json';
        $themePath = '*/*';
        $dictionary = ['hello' => 'bonjour'];
        $areaCode = $data['area_code'];

        $area = $this->createMock(Area::class);
        $area->expects($expects['area_load'])->method('load')->willReturnSelf();

        $chain->expects($this->once())
            ->method('getTargetAssetPath')
            ->willReturn($targetPath);
        $this->configMock->expects($this->once())
            ->method('getDictionaryFileName')
            ->willReturn($fileName);
        $chain->expects($this->once())
            ->method('getAsset')
            ->willReturn($asset);
        $asset->expects($this->once())
            ->method('getContext')
            ->willReturn($context);
        $context->expects($this->once())
            ->method('getThemePath')
            ->willReturn($themePath);
        $context->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $context->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $this->designMock->expects($this->once())->method('setDesignTheme')->with($themePath, $areaCode);

        $this->areaListMock->expects($expects['areaList_getArea'])
            ->method('getArea')
            ->with($areaCode)
            ->willReturn($area);

        $this->dataProviderMock->expects($this->once())
            ->method('getData')
            ->with($themePath)
            ->willReturn($dictionary);
        $chain->expects($this->once())
            ->method('setContent')
            ->with(json_encode($dictionary));
        $chain->expects($this->once())
            ->method('setContentType')
            ->with('json');

        $this->translateMock->expects($this->once())->method('setLocale')->with('en_US')->willReturnSelf();
        $this->translateMock->expects($this->once())->method('loadData')->with($areaCode, true);

        $this->model->process($chain);
    }

    /**
     * Data provider for 'process' method test.
     *
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                [
                    'area_code' => FrontNameResolver::AREA_CODE
                ],
                [
                    'areaList_getArea' => $this->never(),
                    'area_load' => $this->never(),
                ]
            ],
            [
                [
                    'area_code' => 'frontend'
                ],
                [
                    'areaList_getArea' => $this->once(),
                    'area_load' => $this->once(),
                ]
            ],
        ];
    }
}
