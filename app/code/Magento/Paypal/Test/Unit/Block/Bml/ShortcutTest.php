<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Bml;

use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Helper\Data;
use Magento\Paypal\Block\Bml\Shortcut;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Express;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShortcutTest extends TestCase
{
    /** @var Shortcut */
    protected $shortcut;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Data|MockObject */
    protected $paymentHelperMock;

    /** @var Random|MockObject */
    protected $randomMock;

    /** @var ValidatorInterface|MockObject */
    protected $paypalShortcutHelperMock;

    protected function setUp(): void
    {
        $this->paymentHelperMock = $this->createMock(Data::class);
        $this->randomMock = $this->createMock(Random::class);
        $this->paypalShortcutHelperMock = $this->getMockForAbstractClass(ValidatorInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $configFactoryMock = $this->getMockBuilder(ConfigFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setMethod'])
            ->getMock();
        $configFactoryMock->expects($this->any())->method('create')->willReturn($configMock);

        $this->shortcut = $this->objectManagerHelper->getObject(
            Shortcut::class,
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
        $this->shortcut->setShowOrPosition(ShortcutButtons::POSITION_BEFORE);
        $this->assertTrue($this->shortcut->isOrPositionBefore());
    }

    public function testIsOrPositionAfter()
    {
        $this->assertFalse($this->shortcut->isOrPositionAfter());
        $this->shortcut->setShowOrPosition(ShortcutButtons::POSITION_AFTER);
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
        $expressMethod = $this->getMockBuilder(Express::class)
            ->disableOriginalConstructor()->getMock();

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
            ->disableOriginalConstructor()->getMock();
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
