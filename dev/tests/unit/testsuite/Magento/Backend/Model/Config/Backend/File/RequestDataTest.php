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
namespace Magento\Backend\Model\Config\Backend\File;

class RequestDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Backend\File\RequestData
     */
    protected $_model;

    protected function setUp()
    {
        $_FILES = array(
            'groups' => array(
                'name' => array(
                    'group_1' => array('fields' => array('field_1' => array('value' => 'file_name_1'))),
                    'group_2' => array(
                        'groups' => array(
                            'group_2_1' => array('fields' => array('field_2' => array('value' => 'file_name_2')))
                        )
                    )
                ),
                'tmp_name' => array(
                    'group_1' => array('fields' => array('field_1' => array('value' => 'file_tmp_name_1'))),
                    'group_2' => array(
                        'groups' => array(
                            'group_2_1' => array('fields' => array('field_2' => array('value' => 'file_tmp_name_2')))
                        )
                    )
                )
            )
        );

        $this->_model = new \Magento\Backend\Model\Config\Backend\File\RequestData();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testGetNameRetrievesFileName()
    {
        $this->assertEquals('file_name_1', $this->_model->getName('section_1/group_1/field_1'));
        $this->assertEquals('file_name_2', $this->_model->getName('section_1/group_2/group_2_1/field_2'));
    }

    public function testGetTmpNameRetrievesFileName()
    {
        $this->assertEquals('file_tmp_name_1', $this->_model->getTmpName('section_1/group_1/field_1'));
        $this->assertEquals('file_tmp_name_2', $this->_model->getTmpName('section_1/group_2/group_2_1/field_2'));
    }

    public function testGetNameReturnsNullIfInvalidPathIsProvided()
    {
        $this->assertNull($this->_model->getName('section_1/group_2/field_1'));
        $this->assertNull($this->_model->getName('section_1/group_3/field_1'));
        $this->assertNull($this->_model->getName('section_1/group_1/field_2'));
        $this->assertNull($this->_model->getName('section_1/group_1'));
    }
}
