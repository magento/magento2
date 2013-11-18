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
 * @category    Magento
 * @package     Magento_Code
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Code\Plugin;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'GeneratorTest' . DIRECTORY_SEPARATOR . 'SimpleClass.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'GeneratorTest' . DIRECTORY_SEPARATOR . 'SimpleClassPluginA.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'GeneratorTest' . DIRECTORY_SEPARATOR . 'SimpleClassPluginB.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'GeneratorTest' . DIRECTORY_SEPARATOR . 'SimpleObjectManager.php';

class InvocationChainTest extends \PHPUnit_Framework_TestCase
{
    public function testProceed()
    {
        $invocationChain = new \Magento\Code\Plugin\InvocationChain(
            new \Magento\Code\Plugin\GeneratorTest\SimpleClass(),
            'doWork',
            new \Magento\Code\Plugin\GeneratorTest\SimpleObjectManager(),
            array('Magento\Code\Plugin\GeneratorTest\SimpleClassPluginA',
                  'Magento\Code\Plugin\GeneratorTest\SimpleClassPluginB')
        );
        $this->assertEquals(
            '<PluginA><PluginB>simple class return value</PluginB></PluginA>',
            $invocationChain->proceed(array())
        );
    }
}
