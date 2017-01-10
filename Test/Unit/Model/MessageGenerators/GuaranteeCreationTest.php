<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\MessageGenerators;

use Magento\Framework\Phrase;
use Magento\Signifyd\Model\MessageGenerators\GuaranteeCreation;

/**
 * Contains tests for guarantee message generator.
 */
class GuaranteeCreationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Checks cases for generating message based on input data.
     *
     * @covers \Magento\Signifyd\Model\MessageGenerators\GuaranteeCreation::generate
     * @param array $data
     * @param string $message
     * @dataProvider dataProvider
     */
    public function testGenerate(array $data, $message)
    {
        $generator = new GuaranteeCreation();
        $message = $generator->generate($data);

        static::assertEquals($message, $message);
        static::assertInstanceOf(Phrase::class, $message);
    }

    /**
     * Gets list of variations for input data.
     *
     * @return array
     */
    public function dataProvider()
    {
        $message = 'Case Update: Case is submitted for guarantee.';
        return [
            [[], $message],
            [['caseId' => 123], $message],
        ];
    }
}
