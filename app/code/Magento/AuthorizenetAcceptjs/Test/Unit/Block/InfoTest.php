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
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    public function testLabelsAreTranslated()
    {
        /** @var Context|MockObject|InvocationMocker $contextMock */
        $contextMock = $this->createMock(Context::class);
        /** @var Config|MockObject|InvocationMocker $configMock */
        $configMock = $this->createMock(ConfigInterface::class);
        $block = new Info($contextMock, $configMock);
        /** @var InfoInterface|MockObject|InvocationMocker $payment */
        $payment = $this->createMock(InfoInterface::class);
        /** @var RendererInterface|MockObject|InvocationMocker $translationRenderer */
        $translationRenderer = $this->createMock(RendererInterface::class);

        // only foo should be used
        $configMock->method('getValue')
            ->willReturnMap([
                ['paymentInfoKeys', null,  'foo'],
                ['privateInfoKeys', null, '']
            ]);

        // Give more info to ensure only foo is translated
        $payment->method('getAdditionalInformation')
            ->willReturnCallback(function ($name = null) {
                $info = [
                    'foo' => 'bar',
                    'baz' => 'bash'
                ];

                if (empty($name)) {
                    return $info;
                }

                return $info[$name];
            });

        // Foo should be translated to Super Cool String
        $translationRenderer->method('render')
            ->with(['foo'], [])
            ->willReturn('Super Cool String');

        $previousRenderer = Phrase::getRenderer();
        Phrase::setRenderer($translationRenderer);

        try {
            $block->setData('info', $payment);

            $info = $block->getSpecificInformation();
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
