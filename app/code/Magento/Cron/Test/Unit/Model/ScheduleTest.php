<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model;

use Magento\Cron\Model\Schedule;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class \Magento\Cron\Test\Unit\Model\ObserverTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScheduleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helper;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule
     */
    protected $resourceJobMock;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timezoneConverter;

    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->helper = new ObjectManager($this);

        $this->resourceJobMock = $this->getMockBuilder(\Magento\Cron\Model\ResourceModel\Schedule::class)
            ->disableOriginalConstructor()
            ->setMethods(['trySetJobUniqueStatusAtomic', '__wakeup', 'getIdFieldName'])
            ->getMockForAbstractClass();

        $this->resourceJobMock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id'));

        $this->timezoneConverter = $this->getMockBuilder(TimezoneInterface::class)
            ->setMethods(['date'])
            ->getMockForAbstractClass();

        $this->dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->setMethods(['create'])
            ->getMock();
    }

    /**
     * Test for SetCronExpr
     *
     * @param string $cronExpression
     * @param array $expected
     *
     * @return void
     * @dataProvider setCronExprDataProvider
     */
    public function testSetCronExpr($cronExpression, $expected): void
    {
        // 1. Create mocks
        /** @var Schedule $model */
        $model = $this->helper->getObject(Schedule::class);

        // 2. Run tested method
        $model->setCronExpr($cronExpression);

        // 3. Compare actual result with expected result
        $result = $model->getCronExprArr();
        $this->assertEquals($result, $expected);
    }

    /**
     * Data provider
     *
     * Here is a list of allowed characters and values for Cron expression
     * http://docs.oracle.com/cd/E12058_01/doc/doc.1014/e12030/cron_expressions.htm
     *
     * @return array
     */
    public function setCronExprDataProvider(): array
    {
        return [
            ['1 2 3 4 5', [1, 2, 3, 4, 5]],
            ['1 2 3 4 5 6', [1, 2, 3, 4, 5, 6]],
            ['a b c d e', ['a', 'b', 'c', 'd', 'e']],   //should fail if validation will be added
            ['* * * * *', ['*', '*', '*', '*', '*']],

            ['0 * * * *', ['0', '*', '*', '*', '*']],
            ['59 * * * *', ['59', '*', '*', '*', '*']],
            [', * * * *', [',', '*', '*', '*', '*']],
            ['1-2 * * * *', ['1-2', '*', '*', '*', '*']],
            ['0/5 * * * *', ['0/5', '*', '*', '*', '*']],

            ['* 0 * * *', ['*', '0', '*', '*', '*']],
            ['* 59 * * *', ['*', '59', '*', '*', '*']],
            ['* , * * *', ['*', ',', '*', '*', '*']],
            ['* 1-2 * * *', ['*', '1-2', '*', '*', '*']],
            ['* 0/5 * * *', ['*', '0/5', '*', '*', '*']],

            ['* * 0 * *', ['*', '*', '0', '*', '*']],
            ['* * 23 * *', ['*', '*', '23', '*', '*']],
            ['* * , * *', ['*', '*', ',', '*', '*']],
            ['* * 1-2 * *', ['*', '*', '1-2', '*', '*']],
            ['* * 0/5 * *', ['*', '*', '0/5', '*', '*']],

            ['* * * 1 *', ['*', '*', '*', '1', '*']],
            ['* * * 31 *', ['*', '*', '*', '31', '*']],
            ['* * * , *', ['*', '*', '*', ',', '*']],
            ['* * * 1-2 *', ['*', '*', '*', '1-2', '*']],
            ['* * * 0/5 *', ['*', '*', '*', '0/5', '*']],
            ['* * * ? *', ['*', '*', '*', '?', '*']],
            ['* * * L *', ['*', '*', '*', 'L', '*']],
            ['* * * W *', ['*', '*', '*', 'W', '*']],
            ['* * * C *', ['*', '*', '*', 'C', '*']],

            ['* * * * 0', ['*', '*', '*', '*', '0']],
            ['* * * * 11', ['*', '*', '*', '*', '11']],
            ['* * * * ,', ['*', '*', '*', '*', ',']],
            ['* * * * 1-2', ['*', '*', '*', '*', '1-2']],
            ['* * * * 0/5', ['*', '*', '*', '*', '0/5']],
            ['* * * * JAN', ['*', '*', '*', '*', 'JAN']],
            ['* * * * DEC', ['*', '*', '*', '*', 'DEC']],
            ['* * * * JAN-DEC', ['*', '*', '*', '*', 'JAN-DEC']],

            ['* * * * * 1', ['*', '*', '*', '*', '*', '1']],
            ['* * * * * 7', ['*', '*', '*', '*', '*', '7']],
            ['* * * * * ,', ['*', '*', '*', '*', '*', ',']],
            ['* * * * * 1-2', ['*', '*', '*', '*', '*', '1-2']],
            ['* * * * * 0/5', ['*', '*', '*', '*', '*', '0/5']],
            ['* * * * * ?', ['*', '*', '*', '*', '*', '?']],
            ['* * * * * L', ['*', '*', '*', '*', '*', 'L']],
            ['* * * * * 6#3', ['*', '*', '*', '*', '*', '6#3']],
            ['* * * * * SUN', ['*', '*', '*', '*', '*', 'SUN']],
            ['* * * * * SAT', ['*', '*', '*', '*', '*', 'SAT']],
            ['* * * * * SUN-SAT', ['*', '*', '*', '*', '*', 'SUN-SAT']],
        ];
    }

    /**
     * Test for SetCronExprException
     *
     * @param string $cronExpression
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CronException
     * @dataProvider setCronExprExceptionDataProvider
     */
    public function testSetCronExprException($cronExpression): void
    {
        // 1. Create mocks
        /** @var Schedule $model */
        $model = $this->helper->getObject(Schedule::class);

        // 2. Run tested method
        $model->setCronExpr($cronExpression);
    }

    /**
     * Data provider
     *
     * Here is a list of allowed characters and values for Cron expression
     * http://docs.oracle.com/cd/E12058_01/doc/doc.1014/e12030/cron_expressions.htm
     *
     * @return array
     */
    public function setCronExprExceptionDataProvider(): array
    {
        return [
            [''],
            [null],
            [false],
            ['1 2 3 4'],
            ['1 2 3 4 5 6 7']
        ];
    }

    /**
     * Test for trySchedule
     *
     * @param int $scheduledAt
     * @param array $cronExprArr
     * @param $expected
     *
     * @return void
     * @dataProvider tryScheduleDataProvider
     */
    public function testTrySchedule($scheduledAt, $cronExprArr, $expected): void
    {
        // 1. Create mocks
        $this->timezoneConverter->method('getConfigTimezone')
            ->willReturn('UTC');

        $this->dateTimeFactory->method('create')
            ->willReturn(new \DateTime());

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(
            \Magento\Cron\Model\Schedule::class,
            [
                'timezoneConverter' => $this->timezoneConverter,
                'dateTimeFactory' => $this->dateTimeFactory,
            ]
        );

        // 2. Set fixtures
        $model->setScheduledAt($scheduledAt);
        $model->setCronExprArr($cronExprArr);

        // 3. Run tested method
        $result = $model->trySchedule();

        // 4. Compare actual result with expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for tryScheduleWithConversionToAdminStoreTime
     *
     * @return void
     */
    public function testTryScheduleWithConversionToAdminStoreTime(): void
    {
        $scheduledAt = '2011-12-13 14:15:16';
        $cronExprArr = ['*', '*', '*', '*', '*'];

        $this->timezoneConverter->method('getConfigTimezone')
            ->willReturn('UTC');

        $this->dateTimeFactory->method('create')
            ->willReturn(new \DateTime());

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(
            \Magento\Cron\Model\Schedule::class,
            [
                'timezoneConverter' => $this->timezoneConverter,
                'dateTimeFactory' => $this->dateTimeFactory,
            ]
        );

        // 2. Set fixtures
        $model->setScheduledAt($scheduledAt);
        $model->setCronExprArr($cronExprArr);

        // 3. Run tested method
        $result = $model->trySchedule();

        // 4. Compare actual result with expected result
        $this->assertTrue($result);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function tryScheduleDataProvider(): array
    {
        $date = '2011-12-13 14:15:16';
        $timestamp = (new \DateTime($date))->getTimestamp();
        $day = 'Monday';
        return [
            [$date, [], false],
            [$date, null, false],
            [$date, false, false],
            [$date, [], false],
            [$date, null, false],
            [$date, false, false],
            [$timestamp, ['*', '*', '*', '*', '*'], true],
            [$timestamp, ['15', '*', '*', '*', '*'], true],
            [$timestamp, ['*', '14', '*', '*', '*'], true],
            [$timestamp, ['*', '*', '13', '*', '*'], true],
            [$timestamp, ['*', '*', '*', '12', '*'], true],
            [(new \DateTime($day))->getTimestamp(), ['*', '*', '*', '*', '1'], true],
        ];
    }

    /**
     * Test for matchCronExpression
     *
     * @param string $cronExpressionPart
     * @param int $dateTimePart
     * @param bool $expectedResult
     *
     * @return void
     * @dataProvider matchCronExpressionDataProvider
     */
    public function testMatchCronExpression($cronExpressionPart, $dateTimePart, $expectedResult): void
    {
        // 1. Create mocks
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(\Magento\Cron\Model\Schedule::class);

        // 2. Run tested method
        $result = $model->matchCronExpression($cronExpressionPart, $dateTimePart);

        // 3. Compare actual result with expected result
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function matchCronExpressionDataProvider(): array
    {
        return [
            ['*', 0, true],
            ['*', 1, true],
            ['*', 59, true],

            ['0,1,20', 0, true],
            ['0,1,20', 1, true],
            ['0,1,20', 20, true],
            ['0,1,22', 2, false],
            ['0,1,*', 2, true],

            ['0-20', 0, true],
            ['0-20', 1, true],
            ['0-20', 20, true],
            ['0-20', 21, false],

            ['*/2', 0, true],
            ['*/2', 2, true],
            ['*/2', 4, true],
            ['*/2', 3, false],
            ['*/20', 40, true],

            ['0-20/5', 0, true],
            ['0-20/5', 5, true],
            ['0-20/5', 10, true],
            ['0-20/5', 21, false],
            ['0-20/5', 25, false],

            ['1/5', 5, false],
            ['5/5', 5, true],
            ['10/5', 10, true],
        ];
    }

    /**
     * Test for matchCronExpressionException
     *
     * @param string $cronExpressionPart
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CronException
     * @dataProvider matchCronExpressionExceptionDataProvider
     */
    public function testMatchCronExpressionException($cronExpressionPart): void
    {
        $dateTimePart = 10;

        // 1 Create mocks
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(\Magento\Cron\Model\Schedule::class);

        // 2. Run tested method
        $model->matchCronExpression($cronExpressionPart, $dateTimePart);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function matchCronExpressionExceptionDataProvider(): array
    {
        return [
            ['1/2/3'],    //Invalid cron expression, expecting 'match/modulus': 1/2/3
            ['1/'],       //Invalid cron expression, expecting numeric modulus: 1/
            ['-'],        //Invalid cron expression
            ['1-2-3'],    //Invalid cron expression, expecting 'from-to' structure: 1-2-3
        ];
    }

    /**
     * Test for GetNumeric
     *
     * @param mixed $param
     * @param int $expectedResult
     *
     * @return void
     * @dataProvider getNumericDataProvider
     */
    public function testGetNumeric($param, $expectedResult): void
    {
        // 1. Create mocks
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(\Magento\Cron\Model\Schedule::class);

        // 2. Run tested method
        $result = $model->getNumeric($param);

        // 3. Compare actual result with expected result
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getNumericDataProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['0', 0],
            [0, 0],
            [1, 1],
            [PHP_INT_MAX, PHP_INT_MAX],
            [1.1, 1.1],

            ['feb', 2],
            ['Feb', 2],
            ['FEB', 2],
            ['february', 2],
            ['febXXX', 2],

            ['wed', 3],
            ['Wed', 3],
            ['WED', 3],
            ['Wednesday', 3],
            ['wedXXX', 3],
        ];
    }

    /**
     * Test for tryLockJobSuccess
     *
     * @return void
     */
    public function testTryLockJobSuccess(): void
    {
        $scheduleId = 1;

        $this->resourceJobMock->expects($this->once())
            ->method('trySetJobUniqueStatusAtomic')
            ->with($scheduleId, Schedule::STATUS_RUNNING, Schedule::STATUS_PENDING)
            ->will($this->returnValue(true));

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(
            \Magento\Cron\Model\Schedule::class,
            [
                'resource' => $this->resourceJobMock
            ]
        );
        $model->setId($scheduleId);
        $this->assertEquals(0, $model->getStatus());

        $model->tryLockJob();

        $this->assertEquals(Schedule::STATUS_RUNNING, $model->getStatus());
    }

    /**
     * Test for tryLockJobFailure
     *
     * @return void
     */
    public function testTryLockJobFailure(): void
    {
        $scheduleId = 1;

        $this->resourceJobMock->expects($this->once())
            ->method('trySetJobUniqueStatusAtomic')
            ->with($scheduleId, Schedule::STATUS_RUNNING, Schedule::STATUS_PENDING)
            ->will($this->returnValue(false));

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(
            \Magento\Cron\Model\Schedule::class,
            [
                'resource' => $this->resourceJobMock
            ]
        );
        $model->setId($scheduleId);
        $this->assertEquals(0, $model->getStatus());

        $model->tryLockJob();

        $this->assertEquals(0, $model->getStatus());
    }
}
