<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\MessageGenerators;

use Magento\Signifyd\Model\MessageGenerators\CaseReview;
use \Magento\Framework\Phrase;

/**
 * Tests for Signifyd CaseReview message generator.
 *
 * Class CaseReviewTest
 */
class CaseReviewTest extends \PHPUnit_Framework_TestCase
{
    private static $data = ['reviewDisposition' => 100];

    /**
     * @var CaseReview
     */
    private $caseReview;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->caseReview = new CaseReview();
    }

    /**
     * Parameter without required attribute reviewDisposition.
     *
     * @expectedException        \Magento\Signifyd\Model\MessageGeneratorException
     * @expectedExceptionMessage The "reviewDisposition" should not be empty
     */
    public function testGenerateException()
    {
        $this->caseReview->generate([]);
    }

    /**
     * Checks interface generated message.
     *
     * @return \Magento\Framework\Phrase
     */
    public function testGenerateMessageInterface()
    {
        $message = $this->caseReview->generate(self::$data);

        $this->assertInstanceOf(Phrase::class, $message);

        return $message;
    }

    /**
     * Generates Case Review message for created Signifyd properly.
     *
     * @depends testGenerateMessageInterface
     * @param Phrase $message
     */
    public function testGenerate(Phrase $message)
    {
        $phrase = __(
            'Case Update: Case Review was completed. Review Deposition is %1.',
            __(self::$data['reviewDisposition'])
        );

        $this->assertEquals($phrase, $message);
    }
}
