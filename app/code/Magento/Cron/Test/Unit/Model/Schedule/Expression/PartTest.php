<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher as ExpressionPartMatcher;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Validator as ExpressionPartValidator;
use Magento\Cron\Test\Unit\Model\AbstractSchedule;

/**
 * Class \Magento\Cron\Test\Unit\Model\Schedule\ExpressionTest
 */
class PartTest extends AbstractSchedule
{
    /**
     * @var Part|\PHPUnit_Framework_MockObject_MockObject
     */
    private $part;

    /**
     * @var ExpressionPartValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $partValidator;

    /**
     * @var ExpressionPartMatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $partMatcher;

    protected function setUp()
    {
        parent::setUp();

        $this->partValidator = $this->getMockBuilder(ExpressionPartValidator::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->partMatcher = $this->getMockBuilder(ExpressionPartMatcher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var Part|\PHPUnit_Framework_MockObject_MockObject $expression */
        $this->part = $this->getMockBuilder(Part::class)
            ->setMethods()
            ->setConstructorArgs(
                [
                    'validator' => $this->partValidator,
                    'matcher' => $this->partMatcher,
                ]
            )
            ->getMock()
        ;
    }

    /**
     * @param string $partValue
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression\Part::setPartValue
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression\Part::getPartValue
     * @dataProvider setPartValueDataProvider
     */
    public function testSetPartValue($partValue)
    {
        $this->part->setPartValue($partValue);

        $this->assertEquals((string)$partValue, $this->part->getPartValue());
        // Test __toString()
        $this->assertEquals((string)$partValue, $this->part);
    }

    /**
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression\Part::validate
     * @dataProvider booleanDataProvider
     * @param $booleanValue
     */
    public function testValidate($booleanValue)
    {
        $this->partValidator->expects($this->once())->method('validate')->will($this->returnValue($booleanValue));

        $this->assertEquals($booleanValue, $this->part->validate());
        // Assert part validator validate method is not called again
        $this->part->validate();
    }

    /**
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression\Part::match
     * @dataProvider booleanDataProvider
     * @param $booleanValue
     */
    public function testMatch($booleanValue)
    {
        $this->partMatcher->expects($this->once())->method('match')->will($this->returnValue($booleanValue));

        $this->assertEquals($booleanValue, $this->part->match($this->getScheduledAtTimestamp()));
    }

    /**
     * @param string $partValue
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression\Part::reset
     * @dataProvider setPartValueDataProvider
     */
    public function testReset($partValue)
    {
        $this->partValidator->expects($this->any())->method('validate')->will($this->returnValue(true));

        $this->part->setPartValue($partValue);
        $this->part->reset();

        $this->assertEquals('', $this->part->getPartValue());
    }

    /**
     * @return array
     */
    public function setPartValueDataProvider()
    {
        $letters = range('A', 'Z');
        $numbers = range(0, 9);
        $symbols = ['*', '?', '/', '#'];

        return array_chunk(array_merge($letters, $numbers, $symbols), 1);
    }
}
