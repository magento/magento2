<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale;

use Magento\Config\Model\Config\Source\Locale\Currency;
use Magento\Framework\App\Area;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

#[
    AppArea(Area::AREA_ADMINHTML),
]
class ConfigTest extends TestCase
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currency = Bootstrap::getObjectManager()->get(Currency::class);
    }

    public function testNicaraguanCurrenciesExistsBoth()
    {
        $options = $this->currency->toOptionArray();
        $values = [];
        foreach ($options as $option) {
            $values[] = $option['value'];
        }
        $this->assertContains('NIO', $values);
        $this->assertContains('NIC', $values);
    }

    #[
        RequiresPhpExtension('intl', '>= 76'),
    ]
    public function testCaribbeanGuilderExists()
    {
        $options = $this->currency->toOptionArray();
        $values = array_column($options, 'value');
        $this->assertContains('XCG', $values);
    }
}
