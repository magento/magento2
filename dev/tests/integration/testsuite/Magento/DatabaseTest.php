<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento;

use Magento\TestFramework\Helper\Bootstrap;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Assure that there are no redundant indexes declared in database
     */
    public function testDuplicateKeys()
    {
        if (!defined('PERCONA_TOOLKIT_BIN_DIR')) {
            $this->markTestSkipped('Path to Percona Toolkit is not specified.');
        }
        $checkerPath = PERCONA_TOOLKIT_BIN_DIR . '/pt-duplicate-key-checker';

        $db = Bootstrap::getInstance()->getBootstrap()->getApplication()->getDbInstance();
        $command = $checkerPath . ' -d ' . $db->getSchema()
            . ' h=' . $db->getHost()['db-host'] . ',u=' . $db->getUser() . ',p=' . $db->getPassword();

        exec($command, $output, $exitCode);
        $this->assertEquals(0, $exitCode);
        $output = implode(PHP_EOL, $output);
        if (preg_match('/Total Duplicate Indexes\s+(\d+)/', $output, $matches)) {
            $this->fail($matches[1] . ' duplicate indexes found.' . PHP_EOL . PHP_EOL . $output);
        }
    }
}
