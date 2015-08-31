<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Php;

use Magento\Framework\App\Utility\Files;
use Magento\TestFramework\Utility\XssOutputValidator;

/**
 * Find not escaped output in phtml templates
 */
class XssPhtmlTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testXssSensitiveOutput()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $xssOutputValidator = new XssOutputValidator();
        $invoker(
            /**
             * Static test will cover the following cases:
             *
             * 1. /\* @escaped \*\/ before output. Output doesn't require escaping. Test is green.
             * 2. /\* @escapeNotVerified \*\/ before output. Output escaping is not checked and
             *    should be verified (Additional command should be provided to run report which will
             *    show list of defects). Test is green.
             * 3. {object}->{suffix}Html{postfix}(). Output is properly escaped. Test is green.
             * 4. Any of p.1-3. Output is not escaped. Test is red.
             *
             * @param string $file
             */
            function ($file) use ($xssOutputValidator) {
                $lines = $xssOutputValidator->getLinesWithXssSensitiveOutput($file);
                $this->assertEmpty(
                    $lines,
                    "Potentially XSS vulnerability. " .
                    "Please verify that output is escaped at lines " . $lines
                );
            },
            Files::init()->getPhtmlFiles()
        );
    }
}
