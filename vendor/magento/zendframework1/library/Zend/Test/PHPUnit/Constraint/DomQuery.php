<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

if (version_compare(PHPUnit_Runner_Version::id(), '4.1', '>=')) {
    include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DomQuery41.php');

    class Zend_Test_PHPUnit_Constraint_DomQuery extends Zend_Test_PHPUnit_Constraint_DomQuery41
    {}
} elseif (version_compare(PHPUnit_Runner_Version::id(), '3.5', '>=')) {
    include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DomQuery37.php');

    class Zend_Test_PHPUnit_Constraint_DomQuery extends Zend_Test_PHPUnit_Constraint_DomQuery37
    {}
} else {
    include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DomQuery34.php');

    class Zend_Test_PHPUnit_Constraint_DomQuery extends Zend_Test_PHPUnit_Constraint_DomQuery34
    {}
}
