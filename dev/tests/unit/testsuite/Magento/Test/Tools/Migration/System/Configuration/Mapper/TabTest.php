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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\Migration\System\Configuration\Mapper;


require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/AbstractMapper.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Tab.php';
/**
 * Test case for \Magento\Tools\Migration\System\Configuration\Mapper\Tab
 */
class TabTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Tab
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = new \Magento\Tools\Migration\System\Configuration\Mapper\Tab();
    }

    protected function tearDown()
    {
        $this->_object = null;
    }

    public function testTransform()
    {
        $config = array(
            'tab_1' => array(
                'sort_order' => array('#text' => 10),
                'frontend_type' => array('#text' => 'text'),
                'class' => array('#text' => 'css class'),
                'label' => array('#text' => 'tab label'),
                'comment' => array('#cdata-section' => 'tab comment')
            ),
            'tab_2' => array()
        );

        $expected = array(
            array(
                'nodeName' => 'tab',
                '@attributes' => array('id' => 'tab_1', 'sortOrder' => 10, 'type' => 'text', 'class' => 'css class'),
                'parameters' => array(
                    array('name' => 'label', '#text' => 'tab label'),
                    array('name' => 'comment', '#cdata-section' => 'tab comment')
                )
            ),
            array('nodeName' => 'tab', '@attributes' => array('id' => 'tab_2'), 'parameters' => array())
        );

        $actual = $this->_object->transform($config);
        $this->assertEquals($expected, $actual);
    }
}
