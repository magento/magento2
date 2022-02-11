<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Model\Quote;

use Magento\Framework\DB\Helper;
use Magento\TestFramework\Helper\Bootstrap;

class DatetimeDiffPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var DatetimeDiffPlugin
     */
    private $plugin;

    /**
     * @var \Closure
     */
    private $proceed;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->helper = Bootstrap::getObjectManager()->create(
            Helper::class,
            ['modulePrefix' => 'core']
        );
        $this->plugin = Bootstrap::getObjectManager()->create(
            DatetimeDiffPlugin::class
        );
        $this->proceed = function () {
            $this->proceed;
        };
    }

    /**
     * Check the mysql time difference querystring with days
     */
    public function testAroundGetDateDiff()
    {
        $diff = $this->plugin->aroundGetDateDiff($this->helper, $this->proceed, '2022-02-10', '2022-02-11');
        $this->assertInstanceOf('Zend_Db_Expr', $diff);
        $this->assertStringContainsString('ABS(TIMESTAMPDIFF(DAY', (string)$diff);
    }
}
