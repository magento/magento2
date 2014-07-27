<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Cron
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cron\Model;

/**
 * Class \Magento\Cron\Model\ObserverTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;

    protected $resourceJobMock;

    public function setUp()
    {
        $this->helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->resourceJobMock = $this->getMockBuilder('Magento\Cron\Model\Resource\Schedule')
            ->disableOriginalConstructor()
            ->setMethods(['trySetJobStatusAtomic', '__wakeup', 'getIdFieldName'])
            ->getMockForAbstractClass();

        $this->resourceJobMock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id'));
    }

    /**
     * @param string $cronExpression
     * @param array $expected
     * @dataProvider setCronExprDataProvider
     */
    public function testSetCronExpr($cronExpression, $expected)
    {
        // 1. Create mocks
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject('Magento\Cron\Model\Schedule');

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
    public function setCronExprDataProvider()
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
     * @param string $cronExpression
     * @expectedException \Magento\Cron\Exception
     * @dataProvider setCronExprExceptionDataProvider
     */
    public function testSetCronExprException($cronExpression)
    {
        // 1. Create mocks
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject('Magento\Cron\Model\Schedule');

        // 2. Run tested method
        $model->setCronExpr($cronExpression);
    }

    /**
     * Here is a list of allowed characters and values for Cron expression
     * http://docs.oracle.com/cd/E12058_01/doc/doc.1014/e12030/cron_expressions.htm
     *
     * @return array
     */
    public function setCronExprExceptionDataProvider()
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
     * @param int $scheduledAt
     * @param array $cronExprArr
     * @param $expected
     * @dataProvider tryScheduleDataProvider
     */
    public function testTrySchedule($scheduledAt, $cronExprArr, $expected)
    {
        // 1. Create mocks
        $date = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(
            'Magento\Cron\Model\Schedule',
            [
                'date' => $date
            ]
        );

        // 2. Set fixtures
        $model->setScheduledAt($scheduledAt);
        $model->setCronExprArr($cronExprArr);
        $date->expects($this->any())->method('timestamp')->will($this->returnArgument(0));

        // 3. Run tested method
        $result = $model->trySchedule();

        // 4. Compare actual result with expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function tryScheduleDataProvider()
    {
        $date = '2011-12-13 14:15:16';
        return [
            [$date, [], false],
            [$date, null, false],
            [$date, false, false],
            [$date, [], false],
            [$date, null, false],
            [$date, false, false],
            [$date, ['*', '*', '*', '*', '*'], true],
            [strtotime($date), ['*', '*', '*', '*', '*'], true],
            [strtotime($date), ['15', '*', '*', '*', '*'], true],
            [strtotime($date), ['*', '14', '*', '*', '*'], true],
            [strtotime($date), ['*', '*', '13', '*', '*'], true],
            [strtotime($date), ['*', '*', '*', '12', '*'], true],
            [strtotime('Monday'), ['*', '*', '*', '*', '1'], true],
        ];
    }

    /**
     * @param string $cronExpressionPart
     * @param int $dateTimePart
     * @param bool $expectedResult
     * @dataProvider matchCronExpressionDataProvider
     */
    public function testMatchCronExpression($cronExpressionPart, $dateTimePart, $expectedResult)
    {
        // 1. Create mocks
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject('Magento\Cron\Model\Schedule');

        // 2. Run tested method
        $result = $model->matchCronExpression($cronExpressionPart, $dateTimePart);

        // 3. Compare actual result with expected result
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function matchCronExpressionDataProvider()
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
     * @param string $cronExpressionPart
     * @expectedException \Magento\Cron\Exception
     * @dataProvider matchCronExpressionExceptionDataProvider
     */
    public function testMatchCronExpressionException($cronExpressionPart)
    {
        $dateTimePart = 10;

        // 1 Create mocks
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject('Magento\Cron\Model\Schedule');

        // 2. Run tested method
        $model->matchCronExpression($cronExpressionPart, $dateTimePart);

    }

    /**
     * @return array
     */
    public function matchCronExpressionExceptionDataProvider()
    {
        return [
            ['1/2/3'],    //Invalid cron expression, expecting 'match/modulus': 1/2/3
            ['1/'],       //Invalid cron expression, expecting numeric modulus: 1/
            ['-'],        //Invalid cron expression
            ['1-2-3'],    //Invalid cron expression, expecting 'from-to' structure: 1-2-3
        ];
    }

    /**
     * @param mixed $param
     * @param int $expectedResult
     * @dataProvider getNumericDataProvider
     */
    public function testGetNumeric($param, $expectedResult)
    {
        // 1. Create mocks
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject('Magento\Cron\Model\Schedule');

        // 2. Run tested method
        $result = $model->getNumeric($param);

        // 3. Compare actual result with expected result
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getNumericDataProvider()
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

    public function testTryLockJobSuccess()
    {
        $scheduleId = 1;

        $this->resourceJobMock->expects($this->once())
            ->method('trySetJobStatusAtomic')
            ->with($scheduleId, Schedule::STATUS_RUNNING, Schedule::STATUS_PENDING)
            ->will($this->returnValue(true));

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(
            'Magento\Cron\Model\Schedule',
            [
                'resource' => $this->resourceJobMock
            ]
        );
        $model->setId($scheduleId);
        $this->assertEquals(0, $model->getStatus());

        $model->tryLockJob();

        $this->assertEquals(Schedule::STATUS_RUNNING, $model->getStatus());
    }

    public function testTryLockJobFailure()
    {
        $scheduleId = 1;

        $this->resourceJobMock->expects($this->once())
            ->method('trySetJobStatusAtomic')
            ->with($scheduleId, Schedule::STATUS_RUNNING, Schedule::STATUS_PENDING)
            ->will($this->returnValue(false));

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->helper->getObject(
            'Magento\Cron\Model\Schedule',
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
