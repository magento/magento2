<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Message;

use Magento\Framework\Message\Error;
use Magento\Framework\Message\PhraseFactory;
use PHPUnit\Framework\TestCase;

class PhraseFactoryTest extends TestCase
{
    /**
     * @var PhraseFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new PhraseFactory();
    }

    /**
     * @dataProvider dataProvider
     * @param string $mainMessage
     * @param array $subMessages
     * @param string $separator
     * @param string $expectedResult
     */
    public function testCreate($mainMessage, $subMessages, $separator, $expectedResult)
    {
        $result = (string)$this->factory->create($mainMessage, $subMessages, $separator);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        $subMessage1 = new Error('go jogging');
        $subMessage2 = new Error('paint the wall');
        return [
            'positive case' => [
                'We will %1',
                [$subMessage1, $subMessage2],
                ' and ',
                'We will go jogging and paint the wall',
            ],
            'broken messages' => [
                'We will %1',
                [$subMessage1, 'paint the wall'],
                ' and ',
                'We will go jogging and Cannot render error message!',
            ],
        ];
    }
}
