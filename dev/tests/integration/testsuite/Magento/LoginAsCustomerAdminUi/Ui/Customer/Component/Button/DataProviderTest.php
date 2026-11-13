<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Button;

use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\Type\Translate as TranslateCache;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Translation\Model\ResourceModel\StringUtils;
use PHPUnit\Framework\Attributes\DataProvider as TestDataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Login as Customer button label is translated for the admin interface locale.
 */
class DataProviderTest extends TestCase
{
    private const LOCALE = 'fr_FR';
    private const TRANSLATION = 'Se connecter en tant que client';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var RendererInterface
     */
    private $renderer;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->renderer = Phrase::getRenderer();
        Phrase::setRenderer($this->objectManager->get(RendererInterface::class));
    }

    protected function tearDown(): void
    {
        Phrase::setRenderer($this->renderer);
        $this->objectManager->get(TranslateCache::class)->clean();
    }

    /**
     * @param string $dataProviderType
     * @return void
     */
    #[AppArea(Area::AREA_ADMINHTML)]
    #[AppIsolation(true)]
    #[DbIsolation(true)]
    #[TestDataProvider('dataProviderTypes')]
    public function testLabelIsTranslated(string $dataProviderType): void
    {
        $this->objectManager->create(StringUtils::class)
            ->saveTranslate('Login as Customer', self::TRANSLATION, self::LOCALE, 0);
        $this->objectManager->get(TranslateInterface::class)
            ->setLocale(self::LOCALE)
            ->loadData(Area::AREA_ADMINHTML, true);

        /** @var DataProvider $dataProvider */
        $dataProvider = $this->objectManager->create($dataProviderType);

        $this->assertSame(self::TRANSLATION, (string)$dataProvider->getData(1)['label']);
    }

    /**
     * @return array
     */
    public static function dataProviderTypes(): array
    {
        return [
            'customer edit page' => [
                'Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Control\LoginAsCustomerButton\DataProvider',
            ],
            'order, invoice, shipment and credit memo toolbar' => [
                'Magento\LoginAsCustomerAdminUi\Plugin\Button\ToolbarPlugin\DataProvider',
            ],
        ];
    }
}
