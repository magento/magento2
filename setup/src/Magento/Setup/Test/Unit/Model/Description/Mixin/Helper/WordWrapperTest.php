<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Description\Mixin\Helper;

class WordWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\Description\Mixin\Helper\WordWrapper
     */
    private $wrapper;

    public function setUp()
    {
        $this->wrapper = new \Magento\Setup\Model\Description\Mixin\Helper\WordWrapper();
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
    public function getTestData()
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
