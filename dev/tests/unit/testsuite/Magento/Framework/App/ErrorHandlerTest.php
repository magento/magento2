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

namespace Magento\Framework\App;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ErrorHandler
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new ErrorHandler();
    }

    /**
     * @param int $errorNo
     * @param string $errorStr
     * @param string $errorFile
     * @param bool $expectedResult
     * @dataProvider handlerProvider
     */
    public function testHandler($errorNo, $errorStr, $errorFile, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->object->handler($errorNo, $errorStr, $errorFile, 11));
    }

    public function handlerProvider()
    {
        return [
            [0, 'DateTimeZone::__construct', 0, false],
            [0, 0, 0, false]
        ];
    }

    /**
     * Test handler() method with 'false' result
     *
     * @param int $errorNo
     * @param string $errorPhrase
     * @dataProvider handlerProviderException
     */
    public function testHandlerException($errorNo, $errorPhrase)
    {
        $errorStr = 'test_string';
        $errorFile = 'test_file';
        $errorLine = 'test_error_line';

        $exceptedExceptionMessage = sprintf('%s: %s in %s on line %s', $errorPhrase, $errorStr, $errorFile, $errorLine);
        $this->setExpectedException('Exception', $exceptedExceptionMessage);

        $this->object->handler($errorNo, $errorStr, $errorFile, $errorLine);
    }

    public function handlerProviderException()
    {
        return [
            [E_ERROR, 'Error'],
            [E_WARNING, 'Warning'],
            [E_PARSE, 'Parse Error'],
            [E_NOTICE, 'Notice'],
            [E_CORE_ERROR, 'Core Error'],
            [E_CORE_WARNING, 'Core Warning'],
            [E_COMPILE_ERROR, 'Compile Error'],
            [E_COMPILE_WARNING, 'Compile Warning'],
            [E_USER_ERROR, 'User Error'],
            [E_USER_WARNING, 'User Warning'],
            [E_USER_NOTICE, 'User Notice'],
            [E_STRICT, 'Strict Notice'],
            [E_RECOVERABLE_ERROR, 'Recoverable Error'],
            [E_DEPRECATED, 'Deprecated Functionality'],
            [E_USER_DEPRECATED, 'User Deprecated Functionality'],
            ['42', 'Unknown error (42)']
        ];
    }
}
