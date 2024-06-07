<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description\Mixin\Helper;

use Magento\Setup\Model\Description\Mixin\Helper\WordWrapper;
use PHPUnit\Framework\TestCase;

class WordWrapperTest extends TestCase
{
    /**
     * @var WordWrapper
     */
    private $wrapper;

    protected function setUp(): void
    {
        $this->wrapper = new WordWrapper();
    }

    /**
     * @param array $inputData
     * @param string $expectedResult
     * @dataProvider getTestData
     */
    public function testWrapping($inputData, $expectedResult)
    {
        $this->assertEquals(
            $expectedResult,
            $this->wrapper->wrapWords($inputData['source'], $inputData['words'], $inputData['format'])
        );
    }

    /**
     * @return array
     */
    public static function getTestData()
    {
        return [
            [
                [
                    'source' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                    'words' => [],
                    'format' => '',
                ],
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            ],

            [
                [
                    'source' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                    'words' => ['Lorem'],
                    'format' => '<test>%s</test>',
                ],
                '<test>Lorem</test> ipsum dolor sit amet, consectetur adipiscing elit.'
            ],

            [
                [
                    'source' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                    'words' => ['Lorem', 'consectetur', 'elit'],
                    'format' => '<test>%s</test>',
                ],
                '<test>Lorem</test> ipsum dolor sit amet, <test>consectetur</test> adipiscing <test>elit</test>.'
            ],
        ];
    }
}
