<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Catalog\Block\ShortcutButtons;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Layout;
use Magento\Paypal\Block\Express\InContext\Minicart\SmartButton as MinicartButton;
use Magento\Paypal\Block\Express\InContext\SmartButton as Button;
use Magento\Paypal\Block\Express\Shortcut;
use Magento\Paypal\Helper\Shortcut\Factory;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Observer\AddPaypalShortcutsObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see \Magento\Paypal\Observer\AddPaypalShortcutsObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddPaypalShortcutsObserverTest extends TestCase
{
    const PAYMENT_CODE = 'code';

    const PAYMENT_AVAILABLE = 'isAvailable';

    const PAYMENT_IS_BML = 'isBml';

    /**
     * @param array $blocks
     *
     * @dataProvider dataProviderShortcutsButtons
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAddShortcutsButtons(array $blocks)
    {
        /** @var ShortcutButtons|MockObject $shortcutButtonsMock */
        $shortcutButtonsMock = $this->getMockBuilder(ShortcutButtons::class)
            ->setMethods(['getLayout', 'addShortcut'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ShortcutButtons|MockObject $shortcutButtonsMock */
        $eventMock = $this->getMockBuilder(DataObject::class)
            ->setMethods(
                [
                    'getContainer',
                    'getCheckoutSession',
                    'getIsCatalogProduct',
                    'getOrPosition'
                ]
            )->disableOriginalConstructor()
            ->getMock();

        $eventMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($shortcutButtonsMock);

        $observer = new Observer();
        $observer->setEvent($eventMock);

        /** @var Config|MockObject $paypalConfigMock */
        $paypalConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Factory|MockObject $shortcutFactoryMock */
        $shortcutFactoryMock = $this->getMockBuilder(Factory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $model = new AddPaypalShortcutsObserver(
            $shortcutFactoryMock,
            $paypalConfigMock
        );

        /** @var Layout|MockObject $layoutMock */
        $layoutMock = $this->getMockBuilder(Layout::class)
            ->setMethods(['createBlock'])
            ->disableOriginalConstructor()
            ->getMock();

        $callIndexBlock = 0;
        $callIndexShortcutFactory = 0;
        $callIndexAvailable = 0;
        $callIndexSession = 0;

        foreach ($blocks as $instanceName => $blockData) {
            $params = [];

            $paypalConfigMock->expects(self::at($callIndexAvailable))
                ->method('isMethodAvailable')
                ->with($blockData[self::PAYMENT_CODE])
                ->willReturn($blockData[self::PAYMENT_AVAILABLE]);

            ++$callIndexAvailable;

            if (!$blockData[self::PAYMENT_AVAILABLE]) {
                continue;
            }

            ++$callIndexSession;
            $params['shortcutValidator'] = 'test-shortcut-validator';

            $shortcutFactoryMock->expects(self::at($callIndexShortcutFactory))
                ->method('create')
                ->with('test-checkout-session')
                ->willReturn('test-shortcut-validator');

            ++$callIndexShortcutFactory;

            if (!$blockData[self::PAYMENT_IS_BML]) {
                $params['checkoutSession'] = 'test-checkout-session';
                ++$callIndexSession;
            }

            $blockMock = $this->getMockBuilder(MinicartButton::class)
                ->setMethods(['setIsInCatalogProduct', 'setShowOrPosition'])
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();

            $blockMock->expects(self::once())
                ->method('setIsInCatalogProduct')
                ->willReturnSelf();
            $blockMock->expects(self::once())
                ->method('setShowOrPosition')
                ->willReturnSelf();

            $layoutMock->expects(self::at($callIndexBlock))
                ->method('createBlock')
                ->with($instanceName, '', $params)
                ->willReturn($blockMock);

            ++$callIndexBlock;
        }
        $shortcutButtonsMock->expects(self::exactly($callIndexBlock))
            ->method('addShortcut')
            ->with(self::isInstanceOf(ShortcutInterface::class));
        $shortcutButtonsMock->expects(self::exactly($callIndexBlock))
            ->method('getLayout')
            ->willReturn($layoutMock);
        $eventMock->expects(self::exactly($callIndexSession))
            ->method('getCheckoutSession')
            ->willReturn('test-checkout-session');

        $model->execute($observer);
    }

    /**
     * @return array
     */
    public function dataProviderShortcutsButtons()
    {
        return [
            [
                'blocks1' => [
                    MinicartButton::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPS_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => false,
                    ],
                    Button::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPS_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => false,
                    ],
                    Shortcut::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPP_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => false,
                    ],
                    \Magento\Paypal\Block\Bml\Shortcut::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPP_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => true,
                    ]
                ],
            ],
            [
                'blocks2' => [
                    MinicartButton::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPS_EXPRESS,
                        self::PAYMENT_AVAILABLE => false,
                        self::PAYMENT_IS_BML => false,
                    ],
                    Button::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPS_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => false,
                    ],
                    Shortcut::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPP_EXPRESS,
                        self::PAYMENT_AVAILABLE => false,
                        self::PAYMENT_IS_BML => false,
                    ],
                    \Magento\Paypal\Block\Bml\Shortcut::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPP_EXPRESS,
                        self::PAYMENT_AVAILABLE => false,
                        self::PAYMENT_IS_BML => true,
                    ]
                ],
            ]
        ];
    }
}
