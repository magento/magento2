<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Block\Bml;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ShortcutTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Block\Bml\Shortcut */
    protected $shortcut;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Payment\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentHelperMock;

    /** @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject */
    protected $randomMock;

    /** @var \Magento\Paypal\Helper\Shortcut\ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $paypalShortcutHelperMock;

    protected function setUp()
    {
        $this->paymentHelperMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $this->randomMock = $this->getMock('Magento\Framework\Math\Random');
        $this->paypalShortcutHelperMock = $this->getMock('Magento\Paypal\Helper\Shortcut\ValidatorInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->shortcut = $this->objectManagerHelper->getObject(
            'Magento\Paypal\Block\Bml\Shortcut',
            [
                'paymentData' => $this->paymentHelperMock,
                'mathRandom' => $this->randomMock,
                'shortcutValidator' => $this->paypalShortcutHelperMock,
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
            ->with($paymentMethodCode, $isInCatalog)->will($this->returnValue(false));

        $this->assertEmpty($this->shortcut->toHtml());
    }

    public function testToHtmlMethodNotAvailable()
    {
        $isInCatalog = true;
        $paymentMethodCode = '';
        $bmlMethodCode = '';
        $this->shortcut->setIsInCatalogProduct($isInCatalog);
        $expressMethod = $this->getMockBuilder('Magento\Paypal\Model\Express')->disableOriginalConstructor()
            ->setMethods([])->getMock();

        $this->paypalShortcutHelperMock->expects($this->once())->method('validate')
            ->with($paymentMethodCode, $isInCatalog)->will($this->returnValue(true));
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($bmlMethodCode)
            ->will($this->returnValue($expressMethod));
        $expressMethod->expects($this->once())->method('isAvailable')->will($this->returnValue(false));

        $this->assertEmpty($this->shortcut->toHtml());
    }

    public function testToHtmlMethodSetBmlData()
    {
        $isInCatalog = true;
        $paymentMethodCode = '';
        $bmlMethodCode = '';
        $hash = 'hash';
        $this->shortcut->setIsInCatalogProduct($isInCatalog);
        $expressMethod = $this->getMockBuilder('Magento\Paypal\Model\Express')->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $expectedData = [
            'is_in_catalog_product' => $isInCatalog,
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
            ->with($paymentMethodCode, $isInCatalog)->will($this->returnValue(true));
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($bmlMethodCode)
            ->will($this->returnValue($expressMethod));
        $expressMethod->expects($this->once())->method('isAvailable')->will($this->returnValue(true));
        $this->randomMock->expects($this->once())->method('getUniqueHash')->with('ec_shortcut_bml_')
            ->will($this->returnValue($hash));

        $this->assertEmpty($this->shortcut->toHtml());
        $this->assertContains($expectedData, $this->shortcut->getData());
    }
}
