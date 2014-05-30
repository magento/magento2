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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to test composed JsHint test.
 * Used to ensure, that Magento coding standard rules (sniffs) really do what they are intended to do.
 *
 */
namespace Magento\Test\Js\Exemplar;

class JsHintTest extends \PHPUnit_Framework_TestCase
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
            mkdir(dirname($reportFile), 0777);
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
