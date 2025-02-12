<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Listener;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Util\TestDox\NamePrettifier;

class ExtededTestdox extends \PHPUnit\Util\Printer implements \PHPUnit\Framework\TestListener
{
    /**
     * @var NamePrettifier
     */
    protected $prettifier;

    /**
     * @var string
     */
    protected $testClass = '';

    /**
     * @var integer
     */
    protected $testStatus = false;

    /**
     * @var array
     */
    protected $tests = [];

    /**
     * @var integer
     */
    protected $successful = 0;

    /**
     * @var integer
     */
    protected $warning = 0;

    /**
     * @var integer
     */
    protected $failed = 0;

    /**
     * @var integer
     */
    protected $skipped = 0;

    /**
     * @var integer
     */
    protected $incomplete = 0;

    /**
     * @var integer
     */
    protected $risky = 0;

    /**
     * @var \stdClass
     */
    protected $testTypeOfInterest = \PHPUnit\Framework\TestCase::class;

    /**
     * @var string
     */
    protected $currentTestClassPrettified;

    /**
     * @var string
     */
    protected $currentTestMethodPrettified;

    /**
     * Constructor.
     *
     * @param  resource $out
     */
    public function __construct($out = null)
    {
        parent::__construct($out);

        $this->prettifier = new NamePrettifier();
        $this->startRun();
    }

    /**
     * Flush buffer and close output.
     *
     */
    public function flush(): void
    {
        $this->doEndClass();
        $this->endRun();

        parent::flush();
    }

    /**
     * An error occurred.
     *
     * @param  Test $test
     * @param  \Throwable $e
     * @param  float $time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = BaseTestRunner::STATUS_ERROR;
            $this->failed++;
        }
    }

    /**
     * A warning occurred.
     *
     * @param  Test $test
     * @param  Warning $e
     * @param  float $time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = BaseTestRunner::STATUS_FAILURE;
            $this->warning++;
        }
    }

    /**
     * A failure occurred.
     *
     * @param  Test $test
     * @param  \PHPUnit\Framework\AssertionFailedError $e
     * @param  float $time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = BaseTestRunner::STATUS_FAILURE;
            $this->failed++;
        }
    }

    /**
     * Incomplete test.
     *
     * @param  Test $test
     * @param  \Throwable $e
     * @param  float $time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = BaseTestRunner::STATUS_INCOMPLETE;
            $this->incomplete++;
        }
    }

    /**
     * Skipped test.
     *
     * @param  Test $test
     * @param  \Throwable $e
     * @param  float $time
     * @since  Method available since Release 3.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = BaseTestRunner::STATUS_SKIPPED;
            $this->skipped++;
        }
    }

    /**
     * Risky test.
     *
     * @param  Test $test
     * @param  \Throwable $e
     * @param  float $time
     * @since  Method available since Release 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = BaseTestRunner::STATUS_RISKY;
            $this->risky++;
        }
    }

    /**
     * A testsuite started.
     *
     * @param  TestSuite $suite
     * @since  Method available since Release 2.2.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A testsuite ended.
     *
     * @param  TestSuite $suite
     * @since  Method available since Release 2.2.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test): void
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $class = get_class($test);

            if ($this->testClass != $class) {
                if ($this->testClass != '') {
                    $this->doEndClass();
                }

                $this->currentTestClassPrettified = $this->prettifier->prettifyTestClass($class);
                $this->startClass($class);

                $this->testClass = $class;
                $this->tests = [];
            }
            $this->write('.');
            $this->currentTestMethodPrettified = $this->prettifier->prettifyTestMethod($test->getName(false));

            $this->testStatus = BaseTestRunner::STATUS_PASSED;
        }
    }

    /**
     * A test ended.
     *
     * @param Test $test
     * @param float $time
     */
    public function endTest(Test $test, float $time): void
    {
        if ($test instanceof $this->testTypeOfInterest) {
            if (!isset($this->tests[$this->currentTestMethodPrettified])) {
                $this->tests[$this->currentTestMethodPrettified] = ['success' => 0, 'failure' => 0, 'time' => 0];
            }

            if ($this->testStatus == BaseTestRunner::STATUS_PASSED) {
                $this->tests[$this->currentTestMethodPrettified]['success']++;
            }
            if ($this->testStatus == BaseTestRunner::STATUS_ERROR) {
                $this->tests[$this->currentTestMethodPrettified]['failure']++;
            }
            if ($this->testStatus == BaseTestRunner::STATUS_FAILURE) {
                $this->tests[$this->currentTestMethodPrettified]['failure']++;
            }
            $this->tests[$this->currentTestMethodPrettified]['time'] += $time;
            $this->currentTestClassPrettified = null;
            $this->currentTestMethodPrettified = null;
        }
    }

    /**
     * Handler for 'start run' event.
     *
     */
    protected function startRun()
    {
    }

    /**
     * Handler for 'end run' event.
     *
     */
    protected function endRun()
    {
    }

    /**
     * Handler for 'start class' event.
     *
     * @param  string $name
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function startClass($name)
    {
        $this->write($this->currentTestClassPrettified . '  ');
    }

    /**
     * Handler for 'end class' event.
     *
     * @param  string $name
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function endClass($name)
    {
        $this->write("\n");
    }

    /**
     * @since  Method available since Release 2.3.0
     */
    protected function doEndClass()
    {
        foreach ($this->tests as $name => $data) {
            $check = $data['failure'] == 0 ? ' - [x] ' : ' - [ ] ';
            $this->write(
                "\n" . $check . $name . ($data['failure'] + $data['success'] ==
                0 ? ' (skipped)' : '') . ($data['time'] > 1 ? ' - ' . number_format(
                    $data['time'],
                    2
                ) . "s" : '')
            );
        }

        $this->endClass($this->testClass);
    }
}
