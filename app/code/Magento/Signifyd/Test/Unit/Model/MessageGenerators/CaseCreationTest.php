<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\MessageGenerators;

use Magento\Signifyd\Model\MessageGenerators\CaseCreation;
use Magento\Signifyd\Model\Validators\CaseDataValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Phrase;

/**
 * Tests for Signifyd CaseCreation message generator.
 *
 * Class CaseCreationTest
 */
class CaseCreationTest extends \PHPUnit_Framework_TestCase
{
    private static $data = ['caseId' => 100];

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CaseCreation
     */
    private $caseCreation;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->caseCreation = $this->objectManager->getObject(CaseCreation::class, [
            'caseDataValidator' => new CaseDataValidator()
        ]);
    }

    /**
     * Parameter without required attribute caseId.
     *
     * @expectedException        \Magento\Signifyd\Model\MessageGeneratorException
     * @expectedExceptionMessage The "caseId" should not be empty
     */
    public function testGenerateException()
    {
        $this->caseCreation->generate([]);
    }

    /**
     * Checks interface generated message.
     *
     * @return Phrase
     */
    public function testGenerateMessageInterface()
    {
        $message = $this->caseCreation->generate(self::$data);

        $this->assertInstanceOf(Phrase::class, $message);

        return $message;
    }

    /**
     * Generates case creation message for created Signifyd properly.
     *
     * @depends testGenerateMessageInterface
     * @param Phrase $message
     */
    public function testGenerate(Phrase $message)
    {
        $phrase = __('Signifyd Case %1 has been created for order.', self::$data['caseId']);

        $this->assertEquals($phrase, $message);
    }
}
