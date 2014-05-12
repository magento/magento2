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

class SortingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Mapper\Sorting
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Backend\Model\Config\Structure\Mapper\Sorting();
    }

    public function testMap()
    {
        $tabs = array(
            'tab_1' => array('sortOrder' => 10),
            'tab_2' => array('sortOrder' => 5),
            'tab_3' => array('sortOrder' => 1)
        );

        $sections = array(
            'section_1' => array('sortOrder' => 10),
            'section_2' => array('sortOrder' => 5),
            'section_3' => array('sortOrder' => 1),
            'section_4' => array(
                'sortOrder' => 500,
                'children' => array(
                    'group_1' => array('sortOrder' => 150),
                    'group_2' => array('sortOrder' => 20),
                    'group_3' => array(
                        'sortOrder' => 30,
                        'children' => array(
                            'field_1' => array('sortOrder' => 200),
                            'field_2' => array('sortOrder' => 100),
                            'subGroup' => array(
                                'sortOrder' => 0,
                                'children' => array(
                                    'field_4' => array('sortOrder' => 200),
                                    'field_5' => array('sortOrder' => 100)
                                )
                            )
                        )
                    )
                )
            )
        );

        $data = array('config' => array('system' => array('tabs' => $tabs, 'sections' => $sections)));

        $expected = array(
            'config' => array(
                'system' => array(
                    'tabs' => array(
                        'tab_3' => array('sortOrder' => 1),
                        'tab_2' => array('sortOrder' => 5),
                        'tab_1' => array('sortOrder' => 10)
                    ),
                    'sections' => array(
                        'section_3' => array('sortOrder' => 1),
                        'section_2' => array('sortOrder' => 5),
                        'section_1' => array('sortOrder' => 10),
                        'section_4' => array(
                            'sortOrder' => 500,
                            'children' => array(
                                'group_2' => array('sortOrder' => 20),
                                'group_3' => array(
                                    'sortOrder' => 30,
                                    'children' => array(
                                        'subGroup' => array(
                                            'sortOrder' => 0,
                                            'children' => array(
                                                'field_5' => array('sortOrder' => 100),
                                                'field_4' => array('sortOrder' => 200)
                                            )
                                        ),
                                        'field_2' => array('sortOrder' => 100),
                                        'field_1' => array('sortOrder' => 200)
                                    )
                                ),
                                'group_1' => array('sortOrder' => 150)
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
