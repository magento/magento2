<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\DefaultPath;

class DefaultPathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $parts
     * @param string $code
     * @param string $result
     * @dataProvider dataProviderGetPart
     */
    public function testGetPart($parts, $code, $result)
    {
        $model = new \Magento\Framework\App\DefaultPath\DefaultPath($parts);
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
