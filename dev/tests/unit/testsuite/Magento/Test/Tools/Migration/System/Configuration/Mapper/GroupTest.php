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
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Field.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Group.php';
/**
 * Test case for \Magento\Tools\Migration\System\Configuration\Mapper\Group
 */
class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fieldMapperMock;

    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Group
     */
    protected $_object;

    protected function setUp()
    {
        $this->_fieldMapperMock = $this->getMock(
            'Magento\Tools\Migration\System\Configuration\Mapper\Field',
            array(),
            array(),
            '',
            false
        );

        $this->_object = new \Magento\Tools\Migration\System\Configuration\Mapper\Group($this->_fieldMapperMock);
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_fieldMapperMock = null;
    }

    public function testTransform()
    {
        $config = array(
            'group_1' => array(
                'sort_order' => array('#text' => 10),
                'frontend_type' => array('#text' => 'text'),
                'class' => array('#text' => 'css class'),
                'label' => array('#text' => 'group label'),
                'comment' => array('#cdata-section' => 'group comment'),
                'resource' => array('#text' => 'acl'),
                'fieldset_css' => array('#text' => 'some css class'),
                'clone_fields' => array('#text' => 'some fields'),
                'clone_model' => array('#text' => 'some model'),
                'help_url' => array('#text' => 'some url'),
                'hide_in_single_store_mode' => array('#text' => 'mode'),
                'expanded' => array('#text' => 'yes')
            ),
            'group_2' => array(),
            'group_3' => array('fields' => array('label' => 'label'))
        );


        $expected = array(
            array(
                'nodeName' => 'group',
                '@attributes' => array('id' => 'group_1', 'sortOrder' => 10, 'type' => 'text'),
                'parameters' => array(
                    array('name' => 'class', '#text' => 'css class'),
                    array('name' => 'label', '#text' => 'group label'),
                    array('name' => 'comment', '#cdata-section' => 'group comment'),
                    array('name' => 'resource', '#text' => 'acl'),
                    array('name' => 'fieldset_css', '#text' => 'some css class'),
                    array('name' => 'clone_fields', '#text' => 'some fields'),
                    array('name' => 'clone_model', '#text' => 'some model'),
                    array('name' => 'help_url', '#text' => 'some url'),
                    array('name' => 'hide_in_single_store_mode', '#text' => 'mode'),
                    array('name' => 'expanded', '#text' => 'yes')
                )
            ),
            array('nodeName' => 'group', '@attributes' => array('id' => 'group_2'), 'parameters' => array()),
            array(
                'nodeName' => 'group',
                '@attributes' => array('id' => 'group_3'),
                'parameters' => array(),
                'subConfig' => array('label' => 'label')
            )
        );

        $this->_fieldMapperMock->expects(
            $this->once()
        )->method(
            'transform'
        )->with(
            array('label' => 'label')
        )->will(
            $this->returnArgument(0)
        );

        $actual = $this->_object->transform($config);
        $this->assertEquals($expected, $actual);
    }
}
