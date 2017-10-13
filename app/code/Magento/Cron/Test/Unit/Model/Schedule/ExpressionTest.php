<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Schedule;

use Magento\Cron\Model\ResourceModel\Schedule\Expression;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Matcher as ExpressionMatcher;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Parser as ExpressionParser;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Validator as ExpressionValidator;
use Magento\Cron\Test\Unit\Model\AbstractSchedule;

/**
 * Class \Magento\Cron\Test\Unit\Model\Schedule\ExpressionTest
 */
class ExpressionTest extends AbstractSchedule
{
    /**
     * @var Expression|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expression;

    /**
     * @var ExpressionValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expressionValidator;

    /**
     * @var ExpressionParser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expressionParser;

    /**
     * @var ExpressionMatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expressionMatcher;

    protected function setUp()
    {
        parent::setUp();

        $this->expressionValidator = $this->getMockBuilder(ExpressionValidator::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expressionParser = $this->getMockBuilder(ExpressionParser::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expressionMatcher = $this->getMockBuilder(ExpressionMatcher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var Expression|\PHPUnit_Framework_MockObject_MockObject $expression */
        $this->expression = $this->getMockBuilder(Expression::class)
            ->setMethods()
            ->setConstructorArgs(
                [
                    'expressionValidator' => $this->expressionValidator,
                    'expressionParser' => $this->expressionParser,
                    'expressionMatcher' => $this->expressionMatcher,
                ]
            )
            ->getMock()
        ;
    }

    /**
     * @param string $cronExpr
     * @param $cronExprArr
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression::setCronExpr
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression::getCronExpr
     * @dataProvider validCronExprDataProvider
     */
    public function testSetCronExpr($cronExpr, $cronExprArr)
    {
        $this->expressionParser->expects($this->once())->method('parse')->will($this->returnValue($cronExprArr));
        $this->expressionValidator->expects($this->once())->method('validate')->will($this->returnValue(true));

        $this->expression->setCronExpr($cronExpr);

        $this->assertEquals((string)$cronExpr, $this->expression->getCronExpr());
        // Test __toString()
        $this->assertEquals((string)$cronExpr, $this->expression);
    }

    /**
     * @param string $cronExpr
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression::setCronExpr
     * @expectedException \Magento\Framework\Exception\CronException
     * @dataProvider invalidCronExprDataProvider
     */
    public function testSetCronExprExceptionGetParts($cronExpr)
    {
        $this->expressionParser->expects($this->once())->method('parse')->will($this->returnValue([]));
        $this->expressionValidator->expects($this->never())->method('validate')->will($this->returnValue(false));

        $this->expression->setCronExpr($cronExpr);
    }

    /**
     * @param string $cronExpr
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression::setCronExpr
     * @expectedException \Magento\Framework\Exception\CronException
     * @dataProvider invalidCronExprDataProvider
     */
    public function testSetCronExprExceptionValidate($cronExpr)
    {
        $this->expressionParser->expects($this->once())->method('parse')->will($this->returnValue(true));
        $this->expressionValidator->expects($this->once())->method('validate')->will($this->returnValue(false));

        $this->expression->setCronExpr($cronExpr);
    }

    /**
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression::getParts
     * @dataProvider fullCronExprDataProvider
     * @param $cronExpr
     * @param $cronExprArr
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testGetParts($cronExpr, $cronExprArr)
    {
        $this->expressionParser->expects($this->once())->method('parse')->will($this->returnValue($cronExprArr));

        $this->assertEquals($cronExprArr, $this->expression->getParts());
        // Assert expression parser parse method is not called again
        $this->expression->getParts();
    }

    /**
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression::validate
     * @dataProvider booleanDataProvider
     * @param $booleanValue
     */
    public function testValidate($booleanValue)
    {
        $this->expressionValidator->expects($this->once())->method('validate')->will($this->returnValue($booleanValue));

        $this->assertEquals($booleanValue, $this->expression->validate());
        // Assert expression validator validate method is not called again
        $this->expression->validate();
    }

    /**
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression::match
     * @dataProvider booleanDataProvider
     * @param $booleanValue
     */
    public function testMatch($booleanValue)
    {
        $this->expressionMatcher->expects($this->once())->method('match')->will($this->returnValue($booleanValue));

        $this->assertEquals($booleanValue, $this->expression->match($this->getScheduledAtTimestamp()));
    }

    /**
     * @param string $cronExpr
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression::reset
     * @dataProvider validCronExprDataProvider
     */
    public function testReset($cronExpr)
    {
        $this->expressionParser->expects($this->any())->method('parse')->will($this->returnValue(true));
        $this->expressionValidator->expects($this->any())->method('validate')->will($this->returnValue(true));

        $this->expression->setCronExpr($cronExpr);
        $this->expression->reset();

        $this->assertEquals('', $this->expression->getCronExpr());
    }
}
