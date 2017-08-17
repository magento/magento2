<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to test composed JsHint test.
 * Used to ensure, that Magento coding standard rules (sniffs) really do what they are intended to do.
 *
 */
namespace Magento\Test\Js\Exemplar;

class JsHintTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Inspection\JsHint\Command
     */
    protected static $_cmd = null;

    public static function setUpBeforeClass()
    {
        $reportFile = __DIR__ . '/../../../tmp/js_report.txt';
        $fileName = BP . '/lib/web/mage/mage.js';
        self::$_cmd = new \Magento\TestFramework\Inspection\JsHint\Command($fileName, $reportFile);
    }

    protected function setUp()
    {
        $reportFile = self::$_cmd->getReportFile();
        if (!is_dir(dirname($reportFile))) {
            mkdir(dirname($reportFile));
        }
    }

    protected function tearDown()
    {
        $reportFile = self::$_cmd->getReportFile();
        if (file_exists($reportFile)) {
            unlink($reportFile);
        }
        rmdir(dirname($reportFile));
    }

    public function testCanRun()
    {
        $result = false;
        try {
            $result = self::$_cmd->canRun();
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
        $this->assertTrue($result, true);
    }
}
