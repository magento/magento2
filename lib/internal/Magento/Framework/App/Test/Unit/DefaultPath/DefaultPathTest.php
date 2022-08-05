<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\DefaultPath;

use Magento\Framework\App\DefaultPath\DefaultPath;
use PHPUnit\Framework\TestCase;

class DefaultPathTest extends TestCase
{
    /**
     * @param array $parts
     * @param string $code
     * @param string $result
     * @dataProvider dataProviderGetPart
     */
    public function testGetPart($parts, $code, $result)
    {
        $model = new DefaultPath($parts);
        $this->assertEquals($result, $model->getPart($code));
    }

    /**
     * @return array
     */
    public function dataProviderGetPart()
    {
        return [
            [
                ['code' => 'value'],
                'code',
                'value',
            ],
            [
                ['code' => 'value'],
                'other_code',
                null,
            ],
        ];
    }
}
