<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Listener;

class ExtededTestdox extends \PHPUnit_Util_Printer implements \PHPUnit_Framework_TestListener
{
    /**
     * @var \PHPUnit_Util_TestDox_NamePrettifier
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
     * @var string
     */
    protected $testTypeOfInterest = 'PHPUnit_Framework_TestCase';

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

        $this->prettifier = new \PHPUnit_Util_TestDox_NamePrettifier();
        $this->startRun();
    }

    /**
     * Flush buffer and close output.
     *
     */
    public function flush()
    {
        $this->doEndClass();
        $this->endRun();

        parent::flush();
    }

    /**
     * An error occurred.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  \Exception $e
     * @param  float $time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
            $this->failed++;
        }
    }

    /**
     * A failure occurred.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  \PHPUnit_Framework_AssertionFailedError $e
     * @param  float $time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->failed++;
        }
    }

    /**
     * Incomplete test.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  \Exception $e
     * @param  float $time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->incomplete++;
        }
    }

    /**
     * Skipped test.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  \Exception $e
     * @param  float $time
     * @since  Method available since Release 3.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->skipped++;
        }
    }

    /**
     * Risky test.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  \Exception $e
     * @param  float $time
     * @since  Method available since Release 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addRiskyTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = \PHPUnit_Runner_BaseTestRunner::STATUS_RISKY;
            $this->risky++;
        }
    }

    /**
     * A testsuite started.
     *
     * @param  \PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A testsuite ended.
     *
     * @param  \PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param  \PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
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

            $this->testStatus = \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
        }
    }

    /**
     * A test ended.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  float $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            if (!isset($this->tests[$this->currentTestMethodPrettified])) {
                $this->tests[$this->currentTestMethodPrettified] = ['success' => 0, 'failure' => 0, 'time' => 0];
            }

            if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                $this->tests[$this->currentTestMethodPrettified]['success']++;
            }
            if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR) {
                $this->tests[$this->currentTestMethodPrettified]['failure']++;
            }
            if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
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
            $check = $data['failure'] == 0 ? ' [x] ' : ' [ ] ';
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
