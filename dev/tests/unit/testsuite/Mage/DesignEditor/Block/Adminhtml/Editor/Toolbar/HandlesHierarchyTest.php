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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_HandlesHierarchy
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_HandlesHierarchyTest extends PHPUnit_Framework_TestCase
{

    /**#@+
     * Test handle
     */
    const HANDLE_NAME = 'test_handle_name';
    const HANDLE_LABEL = 'test_handle_label';
    /**#@-*/

    /**
     * @var Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_HandlesHierarchy
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_modelName = 'Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_HandlesHierarchy';

    protected function setUp()
    {
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $helperData = $this->getMock('Mage_Core_Helper_Data', array('escapeHtml'), array(), '', false);
        $helperData->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));

        $layoutMerge = $this->getMock('Mage_Core_Model_Layout_Merge', array('getPageHandleLabel'), array(), '', false);
        $layoutMerge->expects($this->any())
            ->method('getPageHandleLabel')
            ->with($this::HANDLE_NAME)
            ->will($this->returnValue($this::HANDLE_LABEL));

        $layout = $this->getMock('Mage_Core_Model_Layout', array('helper', 'getUpdate'), array(), '', false);
        $layout->expects($this->any())
            ->method('helper')
            ->with('Mage_Core_Helper_Data')
            ->will($this->returnValue($helperData));
        $layout->expects($this->any())
            ->method('getUpdate')
            ->will($this->returnValue($layoutMerge));

        $vdeUrlBuilder = $this->getMock('Mage_DesignEditor_Model_Url_Handle', array('getUrl'), array(), '', false);
        $vdeUrlBuilder->expects($this->any())
            ->method('getUrl')
            ->will($this->returnCallback(
                function($url, $parameter) {
                    $key = key($parameter);
                    return $url . '/' . $key . '/' . $parameter[$key];
                }
            ));
        $data = array(
            'urlBuilder'    => $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false),
            'layout'        => $layout,
            'vdeUrlBuilder' => $vdeUrlBuilder
        );

        $this->_model = $objectManagerHelper->getObject($this->_modelName, $data);
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @dataProvider renderHierarchyData
     * @param array $hierarchy
     * @param string $expectedResult
     */
    public function testRenderHierarchy($hierarchy, $expectedResult)
    {
        $this->_model->setHierarchy($hierarchy);
        $this->assertEquals($expectedResult, $this->_model->renderHierarchy());
    }

    public function testSetSelectedHandle()
    {
        $this->_model->setSelectedHandle($this::HANDLE_NAME);
        $this->assertAttributeEquals($this::HANDLE_NAME, '_selectedHandle', $this->_model);
    }

    /**
     * @dataProvider getSelectedHandleData
     * @param string $selectedHandle
     * @param array $hierarchy
     * @param string $expectedResult
     */
    public function testGetSelectedHandle($selectedHandle, $hierarchy, $expectedResult)
    {
        if ($selectedHandle) {
            $this->_model->setSelectedHandle($this::HANDLE_NAME);
        }
        if (!is_null($hierarchy)) {
            $this->_model->setHierarchy($hierarchy);
        }

        $this->assertEquals($expectedResult, $this->_model->getSelectedHandle());
    }

    public function testGetSelectedHandleLabel()
    {
        $this->_model->setSelectedHandle($this::HANDLE_NAME);
        $this->assertEquals(self::HANDLE_LABEL, $this->_model->getSelectedHandleLabel());
    }

    /**
     * Data provider for getSelectedHandle method
     *
     * @return array
     */
    public function getSelectedHandleData()
    {
        return array(
            'already selected' => array(
                'selected handle' => $this::HANDLE_NAME,
                'hierarchy'       => null,
                'expected result' => $this::HANDLE_NAME
            ),
            'first from hierarchy' => array(
                'selected handle' => null,
                'hierarchy'       => array(
                    'first_page_test'  => array(
                        'name' => 'first_page_name',
                    ),
                    'second_page_test' => array(
                        'name' => 'second_page_name',
                    ),
                ),
                'expected result' => 'first_page_name'
            ),
            'empty hierarchy' => array(
                'selected handle' => null,
                'hierarchy'       => array(),
                'expected result' => null
            )
        );
    }

    /**
     * Data provider for renderHierarchy method
     *
     * @return array
     */
    public function renderHierarchyData()
    {
        $expectedResults = array(
            'empty hierarchy' => '',
            'simple hierarchy' => '<ul><li rel="page_test"><a href="design/page/type/handle/page_test">'
                . 'page_label</a></li></ul>',
            'nested hierarchy' => '<ul><li rel="page_test"><a href="design/page/type/handle/page_test">page_label'
                . '</a><ul><li rel="nested_page_test"><a href="design/page/type/handle/nested_page_test">'
                . 'nested_page_label</a></li></ul></li></ul>'
        );

        return array(
            'empty hierarchy'  => array(
                'hierarchy'       => array(),
                'expected result' => $expectedResults['empty hierarchy']
            ),
            'simple hierarchy' => array(
                'hierarchy' => array(
                    'page_test' => array(
                        'name'     => 'page_name',
                        'label'    => 'page_label',
                        'type'     => 'page_type',
                        'children' => array()
                    )
                ),
                'expected result' => $expectedResults['simple hierarchy']
            ),
            'nested hierarchy' => array(
                'hierarchy' => array(
                    'page_test' => array(
                        'name'     => 'page_name',
                        'label'    => 'page_label',
                        'type'     => 'page_type',
                        'children' => array(
                            'nested_page_test' => array(
                                'name'     => 'nested_page_name',
                                'label'    => 'nested_page_label',
                                'type'     => 'nested_page_type',
                                'children' => array()
                            )
                        )
                    )
                ),
                'expected result' => $expectedResults['nested hierarchy']
            ),
        );
    }

}
