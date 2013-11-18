<?php
/**
 * Unit Test for \Magento\Filesystem\Stream\Mode\Zlib
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Stream\Mode;

class ZlibTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider modesDataProvider
     * @param string $mode
     * @param string $expectedMode
     * @param int $ratio
     * @param string $strategy
     */
    public function testConstructor($mode, $expectedMode, $ratio, $strategy)
    {
        $object = new \Magento\Filesystem\Stream\Mode\Zlib($mode);
        $this->assertEquals($expectedMode, $object->getMode());
        $this->assertEquals($ratio, $object->getRatio());
        $this->assertEquals($strategy, $object->getStrategy());
    }

    /**
     * @return array
     */
    public function modesDataProvider()
    {
        return array(
            'w' => array('w', 'w', 1, ''),
            'w+' => array('w+', 'w+', 1, ''),
            'r9' => array('r9', 'r', 9, ''),
            'a+8' => array('a+8', 'a+', 8, ''),
            'wb+7' => array('wb+7', 'wb+', 7, ''),
            'r9f' => array('r9f', 'r', 9, 'f'),
            'a+8h' => array('a+8h', 'a+', 8, 'h'),
            'wb+7f' => array('wb+7f', 'wb+', 7, 'f'),
        );
    }
}
