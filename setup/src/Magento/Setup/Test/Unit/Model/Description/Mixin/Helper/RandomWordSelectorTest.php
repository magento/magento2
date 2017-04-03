<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Description\Mixin\Helper;

class RandomWordSelectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector
     */
    private $helper;

    public function setUp()
    {
        $this->helper = new \Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector();
    }

    /**
     * @param string $fixtureSource
     * @param int $fixtureCount
     * @dataProvider getTestData
     */
    public function testRandomSelector($fixtureSource, $fixtureCount)
    {
        $randWords = $this->helper->getRandomWords($fixtureSource, $fixtureCount);

        $this->assertCount($fixtureCount, $randWords);

        $fixtureWords = str_word_count($fixtureSource, 1);
        foreach ($randWords as $randWord) {
            $this->assertTrue(in_array($randWord, $fixtureWords));
        }
    }

    public function getTestData()
    {
        return [
            [
                'source' => '
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
                    sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                ',
                'count' => 1
            ],
            [
                'source' => 'Lorem.',
                'count' => 5
            ],
            [
                'source' => '
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
                    sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                ',
                'count' => 3
            ],
        ];
    }
}
