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
namespace Magento\Test\Tools\Dependency;

use Magento\Tools\Dependency\Circular;

class CircularTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Dependency\Circular
     */
    protected $circular;

    protected function setUp()
    {
        $this->circular = new Circular();
    }

    public function testBuildCircularDependencies()
    {
        $dependencies = array(1 => array(2), 2 => array(3, 5), 3 => array(1), 5 => array(2));
        $expectedCircularDependencies = array(
            1 => array(array(1, 2, 3, 1)),
            2 => array(array(2, 3, 1, 2), array(2, 5, 2)),
            3 => array(array(3, 1, 2, 3)),
            5 => array(array(5, 2, 5))
        );
        $this->assertEquals($expectedCircularDependencies, $this->circular->buildCircularDependencies($dependencies));
    }
}
