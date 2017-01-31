<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

/**
 * A test that enforces composer.lock is up to date with composer.json
 */
class ComposerLockTest extends \PHPUnit_Framework_TestCase
{
    public function testUpToDate()
    {
        $hash = hash_file('md5', BP . '/composer.json');
        $lockFilePath = BP . '/composer.lock';
        if (!file_exists($lockFilePath)) {
            $this->markTestSkipped('composer.lock file doesn\'t exist');
        }
        $jsonData = file_get_contents($lockFilePath);
        $json = json_decode($jsonData);
        $this->assertSame($hash, $json->hash, 'composer.lock file is not up to date');
    }
}
