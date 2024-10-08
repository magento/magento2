<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\IAVSResponse;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Paypal\Model\PayflowConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IAVSResponseTest extends TestCase
{
    /**
     * Checks a case when IAVS validator retrieves different response from PayPal.
     *
     * @param int $configValue
     * @param string $iavs
     * @param bool $expected
     * @dataProvider variationsDataProvider
     */
    public function testValidate($configValue, $iavs, $expected)
    {
        $response = new DataObject([
            'iavs' => $iavs
        ]);

        /** @var PayflowConfig|MockObject $config */
        $config = $this->getMockBuilder(PayflowConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Transparent|MockObject $model */
        $model = $this->getMockBuilder(Transparent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model->method('getConfig')
            ->willReturn($config);

        $config->method('getValue')
            ->willReturn($configValue);

        $validator = new IAVSResponse();
        self::assertEquals($expected, $validator->validate($response, $model));
    }

    /**
     * Gets list of different variations like configuration, IAVS value.
     *
     * @return array
     */
    public static function variationsDataProvider()
    {
        return [
            ['configValue' => 1, 'iavs' => 'Y', 'expected' => false],
            ['configValue' => 0, 'iavs' => 'Y', 'expected' => true],
            ['configValue' => 1, 'iavs' => 'N', 'expected' => true],
            ['configValue' => 1, 'iavs' => 'X', 'expected' => true],
            ['configValue' => 0, 'iavs' => 'X', 'expected' => true],
        ];
    }
}
