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
namespace Magento\Backend\Model\Config\Structure\Mapper;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Mapper\Path
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Backend\Model\Config\Structure\Mapper\Path();
    }

    public function testMap()
    {
        $data = array(
            'config' => array(
                'system' => array(
                    'sections' => array(
                        'section_1' => array(
                            'id' => 'section_1',
                            'children' => array(
                                'group_1' => array(
                                    'id' => 'group_1',
                                    'children' => array(
                                        'field_1' => array('id' => 'field_1'),
                                        'group_1.1' => array(
                                            'id' => 'group_1.1',
                                            'children' => array('field_1.2' => array('id' => 'field_1.2'))
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        $expected = array(
            'config' => array(
                'system' => array(
                    'sections' => array(
                        'section_1' => array(
                            'id' => 'section_1',
                            'children' => array(
                                'group_1' => array(
                                    'id' => 'group_1',
                                    'children' => array(
                                        'field_1' => array('id' => 'field_1', 'path' => 'section_1/group_1'),
                                        'group_1.1' => array(
                                            'id' => 'group_1.1',
                                            'children' => array(
                                                'field_1.2' => array(
                                                    'id' => 'field_1.2',
                                                    'path' => 'section_1/group_1/group_1.1'
                                                )
                                            ),
                                            'path' => 'section_1/group_1'
                                        )
                                    ),
                                    'path' => 'section_1'
                                )
                            )
                        )
                    )
                )
            )
        );

        $actual = $this->_model->map($data);
        $this->assertEquals($expected, $actual);
    }
}
