<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Json;

use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\DataProvider;
use Magento\Translation\Model\Json\PreProcessor;

class PreProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PreProcessor
     */
    protected $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var DataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProviderMock;

    /**
     * @var \Magento\Framework\App\AreaList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $areaListMock;

    /**
     * @var \Magento\Framework\TranslateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Translation\Model\Js\Config', [], [], '', false);
        $this->dataProviderMock = $this->getMock('Magento\Translation\Model\Js\DataProvider', [], [], '', false);
        $this->areaListMock = $this->getMock('Magento\Framework\App\AreaList', [], [], '', false);
        $this->translateMock = $this->getMockForAbstractClass('Magento\Framework\TranslateInterface');
        $this->model = new PreProcessor(
            $this->configMock,
            $this->dataProviderMock,
            $this->areaListMock,
            $this->translateMock
        );
    }

    public function testGetData()
    {
        $chain = $this->getMock('Magento\Framework\View\Asset\PreProcessor\Chain', [], [], '', false);
        $asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $fileName = 'js-translation.json';
        $targetPath = 'path/js-translation.json';
        $themePath = '*/*';
        $dictionary = ['hello' => 'bonjour'];
        $areaCode = 'adminhtml';
        $area = $this->getMock('Magento\Framework\App\Area', [], [], '', false);

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

        $this->areaListMock->expects($this->once())
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

        $this->model->process($chain);
    }
}
