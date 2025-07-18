<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\RemoveAccents;
use PHPUnit\Framework\TestCase;

class RemoveAccentsTest extends TestCase
{
    /**
     * @param string $string
     * @param bool $german
     * @param string $expected
     *
     * @dataProvider removeAccentsDataProvider
     */
    public function testRemoveAccents($string, $german, $expected)
    {
        $filter = new RemoveAccents($german);
        $this->assertEquals($expected, $filter->filter($string));
    }

    /**
     * @return array
     */
    public static function removeAccentsDataProvider()
    {
        return [
            'general conversion' => ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', false, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            'conversion with german specifics' => ['äöüÄÖÜß', true, 'aeoeueAeOeUess']
        ];
    }
}
