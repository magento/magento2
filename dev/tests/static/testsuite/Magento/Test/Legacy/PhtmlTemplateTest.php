<?php
/**
 * Backwards-incompatible changes in file system
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Legacy;

class PhtmlTemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testBlockVariableInsteadOfThis()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
        /**
         * Test usage of methods and variables in template through $this
         *
         * @param string $file
         */
            function ($file) {
                $this->assertNotRegExp(
                    '/this->(?!helper)\S*/iS',
                    file_get_contents($file),
                    'Access to members and methods of Block class through $this is ' .
                    'obsolete in phtml templates. Use only $block instead of $this.'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }

    public function testObsoleteBlockMethods()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test usage of protected and private methods and variables in template
             *
             * According to naming convention (B5.8, B6.2) all class members
             * in protected or private scope should be prefixed with underscore.
             * Member variables declared "public" should never start with an underscore.
             * Access to protected and private members of Block class is obsolete in phtml templates
             * since introduction of multiple template engines support
             *
             * @param string $file
             */
            function ($file) {
                $this->assertNotRegexp(
                    '/block->_[^_]+\S*\(/iS',
                    file_get_contents($file),
                    'Access to protected and private members of Block class is ' .
                    'obsolete in phtml templates. Use only public members.'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }

    public function testObsoleteJavascriptAttributeType()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
        /**
         * "text/javascript" type attribute in not obligatory to use in templates due to HTML5 standards.
         * For more details please go to "http://www.w3.org/TR/html5/scripting-1.html".
         *
         * @param string $file
         */
            function ($file) {
                $this->assertNotRegexp(
                    '/type="text\/javascript"/',
                    file_get_contents($file),
                    'Please do not use "text/javascript" type attribute.'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }
}
