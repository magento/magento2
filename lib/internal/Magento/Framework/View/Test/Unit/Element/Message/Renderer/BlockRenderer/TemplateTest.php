<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer\BlockRenderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCacheKeyInfo()
    {
        $helper = new ObjectManager($this);
        $storeMock = $this->getMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects(static::once())
            ->method('getStore')
            ->willReturn($storeMock);

        /** @var Template $template */
        $template = $helper->getObject(
            \Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template::class,
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
