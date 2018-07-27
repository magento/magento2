<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Js;

use Magento\Translation\Model\Js\PreProcessor;
use Magento\Translation\Model\Js\Config;
use Magento\Framework\App\AreaList;
use Magento\Framework\TranslateInterface;

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
     * @var AreaList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $areaListMock;

    /**
     * @var TranslateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Translation\Model\Js\Config', [], [], '', false);
        $this->areaListMock = $this->getMock('Magento\Framework\App\AreaList', [], [], '', false);
        $this->translateMock = $this->getMockForAbstractClass('Magento\Framework\TranslateInterface');
        $this->model = new PreProcessor($this->configMock, $this->areaListMock, $this->translateMock);
    }

    public function testGetData()
    {
        $asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $chain = $this->getMock('Magento\Framework\View\Asset\PreProcessor\Chain', [], [], '', false);
        $context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $originalContent = 'content$.mage.__("hello1")content';
        $translatedContent = 'content"hello1"content';
        $patterns = ['~\$\.mage\.__\([\'|\"](.+?)[\'|\"]\)~'];
        $areaCode = 'adminhtml';
        $area = $this->getMock('Magento\Framework\App\Area', [], [], '', false);

        $chain->expects($this->once())
            ->method('getAsset')
            ->willReturn($asset);
        $asset->expects($this->once())
            ->method('getContext')
            ->willReturn($context);
        $context->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);

        $this->configMock->expects($this->once())
            ->method('isEmbeddedStrategy')
            ->willReturn(true);
        $chain->expects($this->once())
            ->method('getContent')
            ->willReturn($originalContent);
        $this->configMock->expects($this->once())
            ->method('getPatterns')
            ->willReturn($patterns);

        $this->areaListMock->expects($this->once())
            ->method('getArea')
            ->with($areaCode)
            ->willReturn($area);

        $chain->expects($this->once())
            ->method('setContent')
            ->with($translatedContent);

        $this->model->process($chain);
    }
}
