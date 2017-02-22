<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;

/**
 * Class AddPaypalShortcutsObserverTest
 */
class AddPaypalShortcutsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Observer\AddPaypalShortcutsObserver
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_event;

    /**
     * @var \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalConfigMock;

    protected function setUp()
    {
        $this->_event = new \Magento\Framework\DataObject();

        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->paypalConfigMock = $this->getMock(
            'Magento\Paypal\Model\Config',
            [],
            [],
            '',
            false
        );
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Paypal\Observer\AddPaypalShortcutsObserver',
            [
                'paypalConfig' => $this->paypalConfigMock,
            ]
        );
    }

    /**
     * @param array $paymentMethodsAvailability
     * @param array $blocks
     * @dataProvider addAvailabilityOfMethodsDataProvider
     */
    public function testAddPaypalShortcuts($paymentMethodsAvailability, $blocks)
    {
        $this->paypalConfigMock->expects($this->any())
            ->method('isMethodAvailable')
            ->will($this->returnValueMap($paymentMethodsAvailability));

        $layoutMock = $this->getMockBuilder(
            'Magento\Framework\View\Layout'
        )->setMethods(
            ['createBlock']
        )->disableOriginalConstructor()->getMock();

        $shortcutButtonsMock = $this->getMockBuilder(
            'Magento\Catalog\Block\ShortcutButtons'
        )->setMethods(
            ['getLayout', 'addShortcut']
        )->disableOriginalConstructor()->getMock();

        $blockInstances = [];
        $atPosition = 0;
        foreach ($blocks as $blockName => $blockInstance) {
            if ($this->paypalConfigMock->isMethodAvailable($blockInstance[1])) {
                $block = $this->getMockBuilder($blockInstance[0])
                    ->setMethods(null)
                    ->disableOriginalConstructor()
                    ->getMock();
                $blockInstances[$blockName] = $block;
                $layoutMock->expects(new MethodInvokedAtIndex($atPosition))->method('createBlock')->with($blockName)
                    ->will($this->returnValue($block));
                $atPosition++;
            }
        }
        $shortcutButtonsMock->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
        $atPosition = 0;
        foreach ($blocks as $blockName => $blockInstance) {
            if ($this->paypalConfigMock->isMethodAvailable($blockInstance[1])) {
                $shortcutButtonsMock->expects(new MethodInvokedAtIndex($atPosition))->method('addShortcut')
                    ->with($this->identicalTo($blockInstances[$blockName]));
                $atPosition++;
            }
        }
        $this->_event->setContainer($shortcutButtonsMock);
        $this->_model->execute($this->_observer);
    }

    public function addAvailabilityOfMethodsDataProvider()
    {
        $blocks = [
            'Magento\Paypal\Block\Express\Shortcut' =>
                ['Magento\Paypal\Block\Express\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS],
            'Magento\Paypal\Block\PayflowExpress\Shortcut' =>
                ['Magento\Paypal\Block\Express\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS],
            'Magento\Paypal\Block\Bml\Shortcut' =>
                ['Magento\Paypal\Block\Bml\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS],
            'Magento\Paypal\Block\Payflow\Bml\Shortcut' =>
                ['Magento\Paypal\Block\Bml\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS],
        ];

        $allMethodsAvailable = [
            [\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS, true],
            [\Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS, true]
        ];

        $allMethodsNotAvailable = [
            [\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS, false],
            [\Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS, false]
        ];

        $firstMethodAvailable = [
            [\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS, true],
            [\Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS, false]
        ];

        $secondMethodAvailable = [
            [\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS, false],
            [\Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS, true]
        ];

        return [
            [$allMethodsAvailable, $blocks],
            [$allMethodsNotAvailable, $blocks],
            [$firstMethodAvailable, $blocks],
            [$secondMethodAvailable, $blocks]
        ];
    }
}
