<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
             * 1. /\* @noEscape \*\/ before output. Output doesn't require escaping. Test is green.
             * 2. /\* @escapeNotVerified \*\/ before output. Output escaping is not checked and
             *    should be verified. Test is green.
             * 3. Methods which contains "html" in their names (e.g. echo $object->{suffix}Html{postfix}() ).
             *    Data is ready for the HTML output. Test is green.
             * 4. AbstractBlock methods escapeHtml, escapeUrl, escapeQuote, escapeXssInUrl are allowed. Test is green.
             * 5. Type casting and php function count() are allowed
             *    (e.g. echo (int)$var, echo (float)$var, echo (bool)$var, echo count($var)). Test is green.
             * 6. Output in single quotes (e.g. echo 'some text'). Test is green.
             * 7. Output in double quotes without variables (e.g. echo "some text"). Test is green.
             * 8. Other of p.1-7. Output is not escaped. Test is red.
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
