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
 * @category    tests
 * @package     static
 * @subpackage  Integrity
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity;

/**
 * Scan source code for dependency of blacklisted classes
 */
class ConcreteImplementationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Classes that should not be injected as dependency in app code
     *
     * @var array
     */
    protected static $_classesBlacklist = null;

    public function testWrongConcreteImplementation()
    {
        self::$_classesBlacklist = file(__DIR__ . '/_files/classes/blacklist.txt', FILE_IGNORE_NEW_LINES);

        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $content = file_get_contents($file);

                if (strpos($content, "namespace Magento\Core") !== false) {
                    return;
                }

                $result = (bool)preg_match(
                    '/function __construct\(([^\)]*)\)/iS',
                    $content,
                    $matches
                );
                if ($result && !empty($matches[1])) {
                    $arguments = explode(',', $matches[1]);
                    foreach ($arguments as $argument) {
                        $type = explode(' ', trim($argument));
                        if (in_array(trim($type[0]), self::$_classesBlacklist)) {
                            $this->fail("Incorrect class dependency found in $file:" . trim($type[0]));
                        }
                    }
                }
            },
            \Magento\TestFramework\Utility\Files::init()->getClassFiles(true, false, false, false, false, false)
        );
    }
}
