<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Catalog\Block\ShortcutButtons;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Layout;
use Magento\Paypal\Block\Express\InContext\Minicart\SmartButton as MinicartButton;
use Magento\Paypal\Block\Express\InContext\SmartButton as Button;
use Magento\Paypal\Block\Express\Shortcut;
use Magento\Paypal\Helper\Shortcut\Factory;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Observer\AddPaypalShortcutsObserver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see \Magento\Paypal\Observer\AddPaypalShortcutsObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddPaypalShortcutsObserverTest extends TestCase
{
    use MockCreationTrait;

    public const PAYMENT_CODE = 'code';

    public const PAYMENT_AVAILABLE = 'isAvailable';

    public const PAYMENT_IS_BML = 'isBml';

    /**
     * @param array $blocks
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[DataProvider('dataProviderShortcutsButtons')]
    public function testAddShortcutsButtons(array $blocks): void
    {
        /** @var ShortcutButtons|MockObject $shortcutButtonsMock */
        $shortcutButtonsMock = $this->createMock(ShortcutButtons::class);

        /** @var ShortcutButtons|MockObject $shortcutButtonsMock */
        $eventMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getContainer', 'getCheckoutSession', 'getIsCatalogProduct', 'getOrPosition']
        );

        $eventMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($shortcutButtonsMock);

        $observer = new Observer();
        $observer->setEvent($eventMock);

        /** @var Config|MockObject $paypalConfigMock */
        $paypalConfigMock = $this->createMock(Config::class);
        /** @var Factory|MockObject $shortcutFactoryMock */
        $shortcutFactoryMock = $this->createMock(Factory::class);

        $model = new AddPaypalShortcutsObserver(
            $shortcutFactoryMock,
            $paypalConfigMock
        );

        /** @var Layout|MockObject $layoutMock */
        $layoutMock = $this->createMock(Layout::class);

        $callIndexBlock = 0;
        $callIndexShortcutFactory = 0;
        $callIndexAvailable = 0;
        $callIndexSession = 0;

        $paypalConfigMockWithArgs = $paypalConfigMockReturnArgs = [];
        $shortcutFactoryMockWithArgs = $shortcutFactoryMockReturnArgs = [];
        $layoutMockWithArgs = $layoutMockReturnArgs = [];

        foreach ($blocks as $instanceName => $blockData) {
            $params = [];
            $paypalConfigMockWithArgs[] = [$blockData[self::PAYMENT_CODE]];
            $paypalConfigMockReturnArgs[] = $blockData[self::PAYMENT_AVAILABLE];

            ++$callIndexAvailable;

            if (!$blockData[self::PAYMENT_AVAILABLE]) {
                continue;
            }
            ++$callIndexSession;
            $params['shortcutValidator'] = 'test-shortcut-validator';

            $shortcutFactoryMockWithArgs[] = ['test-checkout-session'];
            $shortcutFactoryMockReturnArgs[] = 'test-shortcut-validator';

            ++$callIndexShortcutFactory;

            if (!$blockData[self::PAYMENT_IS_BML]) {
                $params['checkoutSession'] = 'test-checkout-session';
                ++$callIndexSession;
            }

            $blockMock = $this->createPartialMockWithReflection(
                MinicartButton::class,
                ['setIsInCatalogProduct', 'setShowOrPosition']
            );

            $blockMock->expects(self::once())
                ->method('setIsInCatalogProduct')
                ->willReturnSelf();
            $blockMock->expects(self::once())
                ->method('setShowOrPosition')
                ->willReturnSelf();

            $layoutMockWithArgs[] = [$instanceName, '', $params];
            $layoutMockReturnArgs[] = $blockMock;

            ++$callIndexBlock;
        }
        $paypalConfigMock
            ->method('isMethodAvailable')
            ->willReturnCallback(function ($paypalConfigMockWithArgs) use ($paypalConfigMockReturnArgs) {
                static $callCount = 0;
                $returnValue = $paypalConfigMockReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });
        $shortcutFactoryMock
            ->method('create')
            ->willReturnCallback(function ($shortcutFactoryMockWithArgs) use ($shortcutFactoryMockReturnArgs) {
                static $callCount = 0;
                $returnValue = $shortcutFactoryMockReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });
        $layoutMock
            ->method('createBlock')
            ->willReturnCallback(function ($layoutMockWithArgs) use ($layoutMockReturnArgs) {
                static $callCount = 0;
                $returnValue = $layoutMockReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });

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
    public static function dataProviderShortcutsButtons(): array
    {
        return [
            [
                'blocks' => [
                    MinicartButton::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPS_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => false
                    ],
                    Button::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPS_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => false
                    ],
                    Shortcut::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPP_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => false
                    ],
                    \Magento\Paypal\Block\Bml\Shortcut::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPP_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => true
                    ]
                ]
            ],
            [
                'blocks' => [
                    MinicartButton::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPS_EXPRESS,
                        self::PAYMENT_AVAILABLE => false,
                        self::PAYMENT_IS_BML => false
                    ],
                    Button::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPS_EXPRESS,
                        self::PAYMENT_AVAILABLE => true,
                        self::PAYMENT_IS_BML => false
                    ],
                    Shortcut::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPP_EXPRESS,
                        self::PAYMENT_AVAILABLE => false,
                        self::PAYMENT_IS_BML => false
                    ],
                    \Magento\Paypal\Block\Bml\Shortcut::class => [
                        self::PAYMENT_CODE => Config::METHOD_WPP_EXPRESS,
                        self::PAYMENT_AVAILABLE => false,
                        self::PAYMENT_IS_BML => true
                    ]
                ]
            ]
        ];
    }
}
