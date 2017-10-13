<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\Expression;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Matcher as ExpressionMatcher;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Parser as ExpressionParser;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Validator as ExpressionValidator;
use Magento\Cron\Test\Unit\Model\AbstractSchedule;

/**
 * Class \Magento\Cron\Test\Unit\Model\Schedule\Expression\ParserTest
 */
class ParserTest extends AbstractSchedule
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
            ->setMethods(['getCronExpr'])
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
     * @param array $cronExprArr
     * @covers       \Magento\Cron\Model\ResourceModel\Schedule\Expression\Parser::parse
     * @dataProvider fullCronExprDataProvider
     */
    public function testParse($cronExpr, $cronExprArr)
    {
        $expressionParser = $this->getExpressionParserObject();
        $this->expression->expects($this->any())->method('getCronExpr')->will($this->returnValue($cronExpr));

        $this->assertEquals(count($cronExprArr), count($expressionParser->parse($this->expression)));
    }
}
