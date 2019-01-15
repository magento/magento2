<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Test\Unit;

class RemoveAccentsTest extends \PHPUnit\Framework\TestCase
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
        $filter = new \Magento\Framework\Filter\RemoveAccents($german);
        $this->assertEquals($expected, $filter->filter($string));
    }

    /**
     * @return array
     */
    public function removeAccentsDataProvider()
    {
        return [
            'general conversion' => ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', false, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            'conversion with german specifics' => ['äöüÄÖÜß', true, 'aeoeueAeOeUess']
        ];
    }
}
