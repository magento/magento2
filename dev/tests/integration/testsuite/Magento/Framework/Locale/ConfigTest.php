<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale;

use Magento\Config\Model\Config\Source\Locale\Currency;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

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

    #[
        AppArea('adminhtml'),
        DbIsolation(true),
        AppIsolation(true),
    ]
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
}
