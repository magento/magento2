<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Block\Bml;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express;

class ShortcutTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Paypal\Block\Bml\Shortcut */
    protected $shortcut;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Payment\Helper\Data|\PHPUnit\Framework\MockObject\MockObject */
    protected $paymentHelperMock;

    /** @var \Magento\Framework\Math\Random|\PHPUnit\Framework\MockObject\MockObject */
    protected $randomMock;

    /** @var \Magento\Paypal\Helper\Shortcut\ValidatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $paypalShortcutHelperMock;

    protected function setUp(): void
    {
        $this->paymentHelperMock = $this->createMock(\Magento\Payment\Helper\Data::class);
        $this->randomMock = $this->createMock(\Magento\Framework\Math\Random::class);
        $this->paypalShortcutHelperMock = $this->createMock(\Magento\Paypal\Helper\Shortcut\ValidatorInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $configFactoryMock = $this->getMockBuilder(ConfigFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();

        $configMock = $this->getMockBuilder(Config::class)
                ->disableOriginalConstructor()
                ->setMethods(['setMethod'])
                ->getMock();
        $configFactoryMock->expects($this->any())->method('create')->willReturn($configMock);

        $this->shortcut = $this->objectManagerHelper->getObject(
            \Magento\Paypal\Block\Bml\Shortcut::class,
            [
                'paymentData' => $this->paymentHelperMock,
                'mathRandom' => $this->randomMock,
                'shortcutValidator' => $this->paypalShortcutHelperMock,
                'config' => $configFactoryMock
            ]
        );
    }

    public function testIsOrPositionBefore()
    {
        $this->assertFalse($this->shortcut->isOrPositionBefore());
        $this->shortcut->setShowOrPosition(CatalogBlock\ShortcutButtons::POSITION_BEFORE);
        $this->assertTrue($this->shortcut->isOrPositionBefore());
    }

    public function testIsOrPositionAfter()
    {
        $this->assertFalse($this->shortcut->isOrPositionAfter());
        $this->shortcut->setShowOrPosition(CatalogBlock\ShortcutButtons::POSITION_AFTER);
        $this->assertTrue($this->shortcut->isOrPositionAfter());
    }

    public function testGetAlias()
    {
        $this->assertEmpty($this->shortcut->getAlias());
    }

    public function testToHtmlWrongValidation()
    {
        $isInCatalog = true;
        $paymentMethodCode = '';
        $this->shortcut->setIsInCatalogProduct($isInCatalog);

        $this->paypalShortcutHelperMock->expects($this->once())->method('validate')
            ->with($paymentMethodCode, $isInCatalog)->willReturn(false);

        $this->assertEmpty($this->shortcut->toHtml());
    }

    public function testToHtmlMethodNotAvailable()
    {
        $isInCatalog = true;
        $paymentMethodCode = '';
        $bmlMethodCode = '';
        $this->shortcut->setIsInCatalogProduct($isInCatalog);
        $expressMethod = $this->getMockBuilder(\Magento\Paypal\Model\Express::class)->disableOriginalConstructor()
            ->setMethods([])->getMock();

        $this->paypalShortcutHelperMock->expects($this->once())->method('validate')
            ->with($paymentMethodCode, $isInCatalog)->willReturn(true);
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($bmlMethodCode)
            ->willReturn($expressMethod);
        $expressMethod->expects($this->once())->method('isAvailable')->willReturn(false);

        $this->assertEmpty($this->shortcut->toHtml());
    }

    public function testToHtmlMethodSetBmlData()
    {
        $isInCatalog = true;
        $paymentMethodCode = '';
        $bmlMethodCode = '';
        $hash = 'hash';
        $this->shortcut->setIsInCatalogProduct($isInCatalog);
        $expressMethod = $this->getMockBuilder(Express::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $expectedData = [
            'is_in_catalog_product' => $isInCatalog,
            'module_name' => 'Magento_Paypal',
            'shortcut_html_id' => $hash,
            'checkout_url' => null,
            'image_url' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppcredit-logo-medium.png',
            'additional_link_image' => [
                'href' => 'https://www.securecheckout.billmelater.com/paycapture-content/'
                    . 'fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html',
                'src' => 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_text.png',
            ],
        ];

        $this->paypalShortcutHelperMock->expects($this->once())->method('validate')
            ->with($paymentMethodCode, $isInCatalog)->willReturn(true);
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($bmlMethodCode)
            ->willReturn($expressMethod);
        $expressMethod->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->randomMock->expects($this->once())->method('getUniqueHash')->with('ec_shortcut_bml_')
            ->willReturn($hash);

        $this->assertEmpty($this->shortcut->toHtml());
        $this->assertEquals($expectedData, $this->shortcut->getData());
    }
}
