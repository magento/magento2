<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
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
