<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Block;

use Magento\AuthorizenetAcceptjs\Block\Info;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    public function testLabelsAreTranslated()
    {
        $contextMock = $this->createMock(Context::class);
        $configMock = $this->createMock(ConfigInterface::class);
        $block = new Info($contextMock, $configMock);
        $payment = $this->createMock(InfoInterface::class);
        $translationRenderer = $this->createMock(RendererInterface::class);

        // only foo should be used
        $configMock->method('getValue')
            ->will($this->returnValueMap([
                ['paymentInfoKeys', null,  'foo'],
                ['privateInfoKeys', null, '']
            ]));

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
