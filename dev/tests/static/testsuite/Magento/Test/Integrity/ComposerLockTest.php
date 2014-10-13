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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
