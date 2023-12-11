<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ups\Helper\Config;
use PHPUnit\Framework\TestCase;

/**
 * Config helper Test
 */
class ConfigTest extends TestCase
{
    /**
     * Ups config helper
     *
     * @var Config
     */
    protected $helper;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->helper = $objectManagerHelper->getObject(Config::class);
    }

    /**
     * @param mixed $result
     * @param null|string $type
     * @param string $code
     * @dataProvider getCodeDataProvider
     */
    public function testGetData($result, $type = null, $code = null)
    {
        $this->assertEquals($result, $this->helper->getCode($type, $code));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getCodeDataProvider()
    {
        return [
            [false],
            [false, 'not-exist-type'],
            [false, 'not-exist-type', 'not-exist-code'],
            [false, 'action'],
            [['single' => '3', 'all' => '4'], 'action', ''],
            ['3', 'action', 'single']
        ];
    }
}
