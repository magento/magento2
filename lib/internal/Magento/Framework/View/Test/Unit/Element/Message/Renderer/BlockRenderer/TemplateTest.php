<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer\BlockRenderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    public function testGetCacheKeyInfo()
    {
        $helper = new ObjectManager($this);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
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
