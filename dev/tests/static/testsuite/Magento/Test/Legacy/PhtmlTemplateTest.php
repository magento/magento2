<?php
/**
 * Backwards-incompatible changes in file system
 *
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
namespace Magento\Test\Legacy;

class PhtmlTemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testObsoleteBlockMethods()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
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
                    '/this->_[^_]+\S*\(/iS',
                    file_get_contents($file),
                    'Access to protected and private members of Block class is ' .
                    'obsolete in phtml templates. Use only public members.'
                );
            },
            \Magento\TestFramework\Utility\Files::init()->getPhtmlFiles()
        );
    }
}
