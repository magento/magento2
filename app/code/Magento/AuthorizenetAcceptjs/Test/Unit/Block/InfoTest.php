<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Block;

use Magento\AuthorizenetAcceptjs\Block\Info;
use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for Magento\AuthorizenetAcceptjs\Block\InfoTest
 */
class InfoTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentInfoInterfaceMock;

    /**
     * @var RendererInterface|MockObject
     */
    private $translationRendererMock;

    /**
     * @var Info
     */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->contextMock = $this->createMock(Context::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->paymentInfoInterfaceMock = $this->createMock(InfoInterface::class);
        $this->translationRendererMock = $this->createMock(RendererInterface::class);

        $this->block = $this->objectManagerHelper->getObject(
            Info::class,
            [
                'templateContext' => $this->contextMock,
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testLabelsAreTranslated()
    {
        // Only foo should be used
        $this->configMock->method('getValue')
            ->willReturnMap([
                ['paymentInfoKeys', null,  'foo'],
                ['privateInfoKeys', null, '']
            ]);

        // Give more info to ensure only foo is translated
        $this->paymentInfoInterfaceMock->method('getAdditionalInformation')
            ->willReturnCallback(function ($name = null) {
                $info = [
                    'foo' => 'bar',
                    'baz' => 'bash',
                ];

                if (empty($name)) {
                    return $info;
                }

                return $info[$name];
            });

        // Foo should be translated to Super Cool String
        $this->translationRendererMock->method('render')
            ->with(['foo'], [])
            ->willReturn('Super Cool String');

        $previousRenderer = Phrase::getRenderer();
        Phrase::setRenderer($this->translationRendererMock);

        try {
            $this->block->setData('info', $this->paymentInfoInterfaceMock);

            $info = $this->block->getSpecificInformation();
        } finally {
            // No matter what, restore the renderer
            Phrase::setRenderer($previousRenderer);
        }

        // Assert the label was correctly translated
        $this->assertSame($info['Super Cool String'], 'bar');
        $this->assertArrayNotHasKey('foo', $info);
        $this->assertArrayNotHasKey('baz', $info);
    }
}
