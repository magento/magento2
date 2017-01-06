<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\MessageGenerators;

use Magento\Signifyd\Model\MessageGenerators\GuaranteeCompletion;
use \Magento\Framework\Phrase;

/**
 * Tests for Signifyd GuaranteeCompletion message generator.
 *
 * Class GuaranteeCompletionTest
 */
class GuaranteeCompletionTest extends \PHPUnit_Framework_TestCase
{
    private static $data = ['guaranteeDisposition' => 100];

    /**
     * @var GuaranteeCompletion
     */
    private $guaranteeCompletion;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->guaranteeCompletion = new GuaranteeCompletion();
    }

    /**
     * Parameter without required attribute guaranteeDisposition.
     *
     * @expectedException        \Magento\Signifyd\Model\MessageGeneratorException
     * @expectedExceptionMessage The "guaranteeDisposition" should not be empty
     */
    public function testGenerateException()
    {
        $this->guaranteeCompletion->generate([]);
    }

    /**
     * Checks interface generated Guarantee Completion message.
     *
     * @return Phrase
     */
    public function testGenerateMessageInterface()
    {
        $message = $this->guaranteeCompletion->generate(self::$data);

        $this->assertInstanceOf(Phrase::class, $message);

        return $message;
    }

    /**
     * Generates Guarantee Completion message for created Signifyd properly.
     *
     * @depends testGenerateMessageInterface
     * @param Phrase $message
     */
    public function testGenerate(Phrase $message)
    {
        $phrase = __('Case Update: Guarantee Disposition is %1.', __(self::$data['guaranteeDisposition']));

        $this->assertEquals($phrase, $message);
    }
}
