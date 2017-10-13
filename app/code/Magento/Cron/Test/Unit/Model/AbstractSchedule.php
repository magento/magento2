<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model;

use Magento\Cron\Model\ResourceModel\Schedule\Expression;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Matcher as ExpressionMatcher;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Parser as ExpressionParser;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part as ExpressionPart;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher as ExpressionPartMatcher;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\MatcherFactory as ExpressionPartMatcherFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParserFactory as ExpressionPartNumericFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Parser as ExpressionPartParser;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ParserFactory as ExpressionPartParserFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Validator as ExpressionPartValidator;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ValidatorHandlerFactory
    as ExpressionPartValidatorHandlerFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartFactory as ExpressionPartFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Validator as ExpressionValidator;
use Magento\Cron\Model\ResourceModel\Schedule\ExpressionFactory;

/**
 * Class \Magento\Cron\Test\Unit\Model\AbstractScheduleTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractSchedule extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $helper;

    /**
     * @var string
     */
    private $scheduledtAt = '2011-12-13 14:15:16';

    /**
     * @return \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return string
     */
    public function getScheduledtAt()
    {
        return $this->scheduledtAt;
    }

    /**
     * @return false|int
     */
    public function getScheduledAtTimestamp()
    {
        return strtotime($this->getScheduledtAt());
    }

    protected function setUp()
    {
        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /******************* Expression level **********************/
    /**
     * @param string $cronExpr
     *
     * @return Expression
     */
    protected function getExpressionObject($cronExpr = '')
    {
        $expressionValidator = $this->getExpressionValidatorObject();
        $expressionParser = $this->getExpressionParserObject($cronExpr);
        $expressionMatcher = $this->getExpressionMatcherObject();

        $expression = $this->getMockBuilder(Expression::class)
            ->setMethods(['getCronExpr'])
            ->setConstructorArgs(
                [
                    'expressionValidator' => $expressionValidator,
                    'expressionParser' => $expressionParser,
                    'expressionMatcher' => $expressionMatcher,
                ]
            )
            ->getMock()
        ;

        $expression->expects($this->any())->method('getCronExpr')->will($this->returnValue($cronExpr));

        return $expression;
    }

    /**
     * @param string $cronExpr
     *
     * @return ExpressionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getExpressionFactoryObject($cronExpr = '')
    {
        /** @var ExpressionFactory|\PHPUnit_Framework_MockObject_MockObject $expressionFactory */
        $expressionFactory = $this->getMockBuilder(ExpressionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $expression = $this->getExpressionObject($cronExpr);
        $expressionFactory->expects($this->any())->method('create')->willReturn($expression);

        return $expressionFactory;
    }

    /**
     * @return ExpressionValidator
     */
    protected function getExpressionValidatorObject()
    {
        /** @var ExpressionValidator $expressionValidator */
        $expressionValidator = $this->helper->getObject(ExpressionValidator::class);
        return $expressionValidator;
    }

    /**
     * @param string $cronExpr
     *
     * @return ExpressionParser
     */
    protected function getExpressionParserObject($cronExpr = '')
    {
        /** @var ExpressionParser $expressionParser */
        $expressionParser = $this->helper->getObject(
            ExpressionParser::class,
            [
                'partFactory' => $this->getExpressionPartFactoryObject($cronExpr)
            ]
        );
        return $expressionParser;
    }

    /**
     * @return ExpressionMatcher
     */
    protected function getExpressionMatcherObject()
    {
        /** @var ExpressionMatcher $expressionMatcher */
        $expressionMatcher = $this->helper->getObject(
            ExpressionMatcher::class,
            [
                'matcherFactory' => $this->getExpressionPartMatcherFactoryObject()
            ]
        );
        return $expressionMatcher;
    }

    /**
     * @param int    $indexType
     * @param string $partValue
     *
     * @return ExpressionPart
     */
    protected function getExpressionPartObject($indexType, $partValue)
    {
        $validator = $this->getExpressionPartValidatorObject();
        $parser = $this->getExpressionPartParserObject();
        $matcher = $this->getExpressionPartMatcherObject();

        /** @var ExpressionPart $expressionPart */
        $expressionPart = $this->helper->getObject(
            'Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Index\\' . $indexType,
            [
                'validator' => $validator,
                'parser' => $parser,
                'matcher' => $matcher,
            ]
        );

        $expressionPart->setPartValue($partValue);

        return $expressionPart;
    }

    /**
     * @param string $cronExpr
     *
     * @return ExpressionPartFactory
     */
    protected function getExpressionPartFactoryObject($cronExpr = '')
    {
        /** @var ExpressionPartFactory|\PHPUnit_Framework_MockObject_MockObject $partFactory */
        $partFactory = $this->getMockBuilder(ExpressionPartFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $returnValueMap = [];
        $returnValueMap[] = [
            PartFactory::GENERIC_PART,
            $cronExpr,
            $this->getExpressionPartObject(PartFactory::GENERIC_PART, $cronExpr)
        ];

        $availableValidators = $partFactory->getPartAvailableIndexes();
        $cronExprParts = preg_split('#\s+#', $cronExpr, null, PREG_SPLIT_NO_EMPTY);
        foreach ($cronExprParts as $partIndex => $partValue) {
            $returnValueMap[] = [
                PartFactory::GENERIC_PART,
                $partValue,
                $this->getExpressionPartObject(PartFactory::GENERIC_PART, $partValue)
            ];
            if (isset($availableValidators[$partIndex])) {
                $returnValueMap[] = [
                    $partIndex,
                    $partValue,
                    $this->getExpressionPartObject($availableValidators[$partIndex], $partValue)
                ];
            }
        }

        $partFactory->expects($this->any())->method('create')->willReturnMap($returnValueMap);

        return $partFactory;
    }

    /******************* Expression part level **********************/
    /**
     * @return ExpressionPartValidator
     */
    protected function getExpressionPartValidatorObject()
    {
        /** @var ExpressionPartValidator $expressionValidator */
        $partValidator = $this->helper->getObject(
            ExpressionPartValidator::class,
            [
                'parser' => $this->getExpressionPartParserObject(),
                'validatorHandlerFactory' => $this->getExpressionPartValidatorHandlerFactoryObject(),
            ]
        );
        return $partValidator;
    }

    /**
     * @return ExpressionPartParser
     */
    protected function getExpressionPartParserObject()
    {
        /** @var ExpressionPartParser $expressionParser */
        $partParser = $this->helper->getObject(
            ExpressionPartParser::class,
            [
                'parserFactory' => $this->getExpressionPartParserFactoryObject(),
            ]
        );
        return $partParser;
    }

    /**
     * @return ExpressionPartMatcher
     */
    protected function getExpressionPartMatcherObject()
    {
        /** @var ExpressionPartMatcher $partMatcher */
        $partMatcher = $this->helper->getObject(
            ExpressionPartMatcher::class,
            [
                'parser' => $this->getExpressionPartParserObject(),
                'matcherFactory' => $this->getExpressionPartMatcherFactoryObject(),
                'numericFactory' => $this->getExpressionPartNumericFactoryObject(),
                'validatorHandlerFactory' => $this->getExpressionPartValidatorHandlerFactoryObject(),
            ]
        );
        return $partMatcher;
    }

    /**
     * @return ExpressionPartParserFactory
     */
    protected function getExpressionPartParserFactoryObject()
    {
        /** @var ExpressionPartParserFactory|\PHPUnit_Framework_MockObject_MockObject $parseFactory */
        $parseFactory = $this->getMockBuilder(ExpressionPartParserFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $returnValueMap = [];
        $availableParsers = $parseFactory->getAvailableParsers();
        foreach ($availableParsers as $parserType) {
            $returnValueMap[] = [
                $parserType,
                $this->helper->getObject(
                    'Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Parser\\'
                    . $parserType . 'Parser'
                )
            ];
        }

        $parseFactory->expects($this->any())->method('create')->willReturnMap($returnValueMap);

        return $parseFactory;
    }

    /**
     * @return ExpressionPartMatcherFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getExpressionPartMatcherFactoryObject()
    {
        /** @var ExpressionPartMatcherFactory|\PHPUnit_Framework_MockObject_MockObject $matcherFactory */
        $matcherFactory = $this->getMockBuilder(ExpressionPartMatcherFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $returnValueMap = [];
        $availableMatchers = $matcherFactory->getAvailableMatchers();
        foreach ($availableMatchers as $matcherType) {
            $returnValueMap[] = [
                $matcherType,
                $this->helper->getObject(
                    'Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher\\' . $matcherType
                )
            ];
        }

        $matcherFactory->expects($this->any())->method('create')->willReturnMap($returnValueMap);

        return $matcherFactory;
    }

    /**
     * @return ExpressionPartValidatorHandlerFactory|\PHPUnit_Framework_MockObject_MockObject
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function getExpressionPartValidatorHandlerFactoryObject()
    {
        /** @var ExpressionPartValidatorHandlerFactory|\PHPUnit_Framework_MockObject_MockObject
         * $validatorHandlerFactory */
        $validatorHandlerFactory = $this->getMockBuilder(ExpressionPartValidatorHandlerFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock()
        ;

        $returnValueMap = [];
        $availableValidatorHandlers = $validatorHandlerFactory->getAvailableValidatorHandlers();
        foreach ($availableValidatorHandlers as $validatorHandlerType) {
            $returnValueMap[] = [
                $validatorHandlerType,
                $this->helper->getObject(
                    'Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ValidatorHandler\\'
                    . $validatorHandlerType,
                    [
                        'validatorHandlerFactory' => $validatorHandlerFactory,
                        'parser' => $this->getExpressionPartParserObject(),
                        'numericFactory' => $this->getExpressionPartNumericFactoryObject(),
                    ]
                )
            ];
        }

        $validatorHandlerFactory->expects($this->any())->method('create')->willReturnMap($returnValueMap);

        return $validatorHandlerFactory;
    }

    /**
     * @return ExpressionPartNumericFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getExpressionPartNumericFactoryObject()
    {
        /** @var ExpressionPartNumericFactory|\PHPUnit_Framework_MockObject_MockObject $numericFactory */
        $numericFactory = $this->getMockBuilder(ExpressionPartNumericFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $returnValueMap = [];
        $availableNumerics = $numericFactory->getAvailableNumerics();
        foreach ($availableNumerics as $numericType) {
            $returnValueMap[] = [
                $numericType,
                $this->helper->getObject(
                    'Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParser\\' . $numericType
                )
            ];
        }

        $numericFactory->expects($this->any())->method('create')->willReturnMap($returnValueMap);

        return $numericFactory;
    }

    /**
     * @return array
     */
    public function booleanDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Data provider
     *
     * List of valid expressions for cron expression
     *
     * @return array
     */
    public function validCronExprDataProvider()
    {
        $data = [];
        foreach ($this->fullCronExprDataProvider() as $cronExprData) {
            if ($cronExprData[2] === true) {
                $data[] = $cronExprData;
            }
        }
        return $data;
    }

    /**
     * Data provider
     *
     * List of invalid expressions for cron expression
     *
     * @return array
     */
    public function invalidCronExprDataProvider()
    {
        $data = [];
        foreach ($this->fullCronExprDataProvider() as $cronExprData) {
            if ($cronExprData[2] === false) {
                $data[] = $cronExprData;
            }
        }
        return $data;
    }

    /**
     * Data provider
     *
     * List of valid and invalid expressions for cron expression
     * Array components: string cron expression, parsed array, expected validity, expected match against timestamp
     *
     * @return array
     */
    public function fullCronExprDataProvider()
    {
        return [
            ['* * * * *', ['*', '*', '*', '*', '*'], true, true],
            ['* * * * * *', ['*', '*', '*', '*', '*', '*'], true, true],
            ['1 2 3 4', ['1', '2', '3', '4'], false, false],
            ['58 6 6 10 4 2017', ['58', '6', '6', '10', '4', '2017'], true, false],
            [
                '58/2 6/3 6/4 10/8 4/2 2017/10',
                ['58/2', '6/3', '6/4', '10/8', '4/2', '2017/10'],
                true,
                false
            ],
            [
                '18-39/2 6-16/3 6-16/4 10-12/8 4-5/2 2017-2020',
                ['18-39/2', '6-16/3', '6-16/4', '10-12/8', '4-5/2', '2017-2020'],
                true,
                false
            ],
            [
                '18-39 6-16 6-16 10-12 4-5 2017-2020',
                ['18-39', '6-16', '6-16', '10-12', '4-5', '2017-2020'],
                true,
                false
            ],
            ['* * ? * *', ['*', '*', '?', '*', '*'], true, true],
            ['* * L * *', ['*', '*', 'L', '*', '*'], true, false],
            ['* * * * ?', ['*', '*', '*', '*', '?'], true, true],
            ['* * * * L', ['*', '*', '*', '*', 'L'], false, false],
            ['* * * * 5L', ['*', '*', '*', '*', '5L'], true, false],
            ['* * 5W * *', ['*', '*', '5W', '*', '*'], true, false],
            ['* * * * #', ['*', '*', '*', '*', '#'], false, false],
            ['* * * * 5#', ['*', '*', '*', '*', '5#'], false, false],
            ['* * * * #3', ['*', '*', '*', '*', '#3'], false, false],
            ['* * * * 5#3', ['*', '*', '*', '*', '5#3'], true, false],

            ['', false, false, false],
            [null, false, false, false],
            [false, false, false, false],
            ['0', ['0'], false, false],
            ['* * * *', ['*', '*', '*', '*'], false, false],
            ['* * * * * * *', ['*', '*', '*', '*', '*', '*', '*'], false, false],
            ['1 2 3 4', ['1', '2', '3', '4'], false, false],
            ['1 2 3 4 5 6 7', ['1', '2', '3', '4', '5', '6', '7'], false, false],
            ['a b c d e', ['a', 'b', 'c', 'd', 'e'], false, false],
            [', * * * *', [',', '*', '*', '*', '*'], false, false],
            ['* , * * *', ['*', ',', '*', '*', '*'], false, false],
            ['* * , * *', ['*', '*', ',', '*', '*'], false, false],
            ['* * * , *', ['*', '*', '*', ',', '*'], false, false],
            ['* * * * ,', ['*', '*', '*', '*', ','], false, false],
            ['* * * * * ,', ['*', '*', '*', '*', '*', ','], false, false],
            ['68 6 6 10 * 2017', ['68', '6', '6', '10', '*', '2017'], false, false],
            ['58 36 6 10 * 2017', ['58', '36', '6', '10', '*', '2017'], false, false],
            ['58 6 36 10 * 2017', ['58', '6', '36', '10', '*', '2017'], false, false],
            ['58 6 6 16 * 2017', ['58', '6', '6', '16', '*', '2017'], false, false],
            ['58 6 6 10 8 2017', ['58', '6', '6', '10', '8', '2017'], false, false],
            ['58 6 6 10 * 2117', ['58', '6', '6', '10', '*', '2117'], false, false],
            ['58 6 6 0 * 2017', ['58', '6', '6', '0', '*', '2017'], false, false],
            ['58 6 6 10 * 1917', ['58', '6', '6', '10', '*', '1917'], false, false],
            ['58/60 6/3 6/4 10/8 4/2 2017/10', ['58/60', '6/3', '6/4', '10/8', '4/2', '2017/10'], true, false],
            ['58/2 6/30 6/4 10/8 4/2 2017/10', ['58/2', '6/30', '6/4', '10/8', '4/2', '2017/10'], true, false],
            ['58/2 6/3 6/40 10/8 4/2 2017/10', ['58/2', '6/3', '6/40', '10/8', '4/2', '2017/10'], true, false],
            ['58/2 6/3 6/4 10/80 4/2 2017/10', ['58/2', '6/3', '6/4', '10/80', '4/2', '2017/10'], true, false],
            ['58/2 6/3 6/4 10/8 4/20 2017/10', ['58/2', '6/3', '6/4', '10/8', '4/20', '2017/10'], true, false],
            [
                '39-18/2 16-6/3 16-6/4 12-10/8 5-4/2 2020-2017',
                ['39-18/2', '16-6/3', '16-6/4', '12-10/8', '5-4/2', '2020-2017'],
                false,
                false
            ],
            ['* * * ? *', ['*', '*', '*', '?', '*'], false, false],
            ['* * * L *', ['*', '*', '*', 'L', '*'], false, false],
            ['* * * W *', ['*', '*', '*', 'W', '*'], false, false],
            ['* * * C *', ['*', '*', '*', 'C', '*'], false, false],
            ['* * 5L * *', ['*', '*', '5L', '*', '*'], false, false],
            ['* * W * *', ['*', '*', 'W', '*', '*'], false, false],
            ['* * * * L5', ['*', '*', '*', '*', 'L5'], false, false],
            ['* * * * a#', ['*', '*', '*', '*', 'a#'], false, false],
            ['* * * * #a', ['*', '*', '*', '*', '#a'], false, false],
            ['* * * * a#a', ['*', '*', '*', '*', 'a#a'], false, false],
            ['15 * * * *', ['15', '*', '*', '*', '*'], true, true],
            ['* 14 * * *', ['*', '14', '*', '*', '*'], true, true],
            ['* * 13 * *', ['*', '*', '13', '*', '*'], true, true],
            ['* * * 12 *', ['*', '*', '*', '12', '*'], true, true],
            ['*/15 * * * *', ['*/15', '*', '*', '*', '*'], true, true],
            ['*/4 * * * *', ['*/4', '*', '*', '*', '*'], true, false],
            ['15/15 * * * *', ['15/15', '*', '*', '*', '*'], true, true],
            ['30/15 * * * *', ['30/15', '*', '*', '*', '*'], true, false],
            ['* 30,*/7 * * *', ['*', '30,*/7', '*', '*', '*'], false, false],
            ['* */7,30 * * *', ['*', '*/7/,30', '*', '*', '*'], false, false],
            ['* * 15,*/13 * *', ['*', '*', '15,*/13', '*', '*'], true, true],
            ['* * * */6 *', ['*', '*', '*', '*/6', '*'], true, true],
            ['* * * * Monday', ['*', '*', '*', '*', 'Monday'], true, false],
            ['* * * * Tuesday', ['*', '*', '*', '*', 'Tuesday'], true, true],
        ];
    }
}
