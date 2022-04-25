<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Js;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\PreProcessor;
use PHPUnit\Framework\TestCase;

class PreProcessorTest extends TestCase
{
    /**
     * @var PreProcessor
     */
    private PreProcessor $model;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var AreaList|MockObject
     */
    private $areaListMock;

    /**
     * @var TranslateInterface|MockObject
     */
    private $translateMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->areaListMock = $this->createMock(AreaList::class);
        $this->translateMock = $this->getMockForAbstractClass(TranslateInterface::class);
        $this->model = new PreProcessor(
            $this->configMock,
            $this->areaListMock,
            $this->translateMock
        );
    }

    /**
     * Test 'process' method.
     *
     */
    public function testProcess()
    {
        $areaCode = 'frontend';
        $themePath = '*/*';

        $chain = $this->createMock(Chain::class);
        $asset = $this->createMock(File::class);
        $context = $this->createMock(FallbackContext::class);
        $area = $this->createMock(Area::class);
        $this->configMock->expects($this->once())
            ->method('isEmbeddedStrategy')
            ->willReturn(1);
        $chain->expects($this->once())
            ->method('getAsset')
            ->willReturn($asset);
        $asset->expects($this->once())
            ->method('getContext')
            ->willReturn($context);
        $context->expects($this->atLeastOnce())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $context->expects($this->atLeastOnce())
            ->method('getLocale')
            ->willReturn('nl_NL');
        $this->translateMock->expects($this->once())
            ->method('setLocale')
            ->with('nl_NL')
            ->willReturnSelf();
        $context->expects($this->atLeastOnce())
            ->method('getThemePath')
            ->willReturn($themePath);
        $this->translateMock->expects($this->once())
            ->method('loadData')
            ->with($areaCode, false)
            ->willReturnSelf();
        $area->expects($this->once())->method('load')->willReturnSelf();
        $this->areaListMock->expects($this->once())
            ->method('getArea')
            ->with($areaCode)
            ->willReturn($area);
        $chain->expects($this->once())
            ->method('getContent')
            ->willReturn('$t("Add to Cart")');
        $this->configMock->expects($this->any())
            ->method('getPatterns')
            ->willReturn(new \ArrayIterator(
                [
                    '~(?s)\$t\(\s*([\'"])(\?\<translate\>.+?)(?<!\\\)\1\s*(*SKIP)\)(?s)~',
                    '~\$\.mage\.__\(([\'"])(.+?)\1\)~'
                ],
            ));
        $this->model->process($chain);
    }
}
