<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer\BlockRenderer;

use PHPUnit\Framework\TestCase;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template;

class TemplateTest extends TestCase
{
    public function testGetCacheKeyInfo()
    {
        $helper = new ObjectManager($this);
        $storeMock = $this->createMock(StoreInterface::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects(static::once())
            ->method('getStore')
            ->willReturn($storeMock);

        /** @var Template $template */
        $template = $helper->getObject(
            Template::class,
            [
                'storeManager' => $storeManager
            ]
        );

        $expectedData = [
            'coconut' => 1,
            'swallow' => 1,
            'MESSAGE',
            'MontyPythonAndTheHolyGrail.phtml',
            'GB'
        ];

        $storeMock->expects(static::once())
            ->method('getCode')
            ->willReturn('GB');
        $template->setTemplate('MontyPythonAndTheHolyGrail.phtml');
        $template->setData(
            [
                'coconut' => 1,
                'swallow' => 1
            ]
        );

        static::assertSame($expectedData, $template->getCacheKeyInfo());
    }
}
