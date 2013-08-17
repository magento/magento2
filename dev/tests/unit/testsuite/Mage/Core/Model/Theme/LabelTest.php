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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme label
 */
class Mage_Core_Model_Theme_LabelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collection;

    /**
     * @var Mage_Core_Model_Theme_Label
     */
    protected $_model;

    protected function setUp()
    {
        $this->_helper = $this->getMock('Mage_Core_Helper_Data', array('__'), array(), '', false);
        $this->_helper->expects($this->any())->method('__')->will($this->returnCallback(function () {
            $arguments = func_get_args();
            return call_user_func_array('sprintf', $arguments);
        }));

        $this->_collection = $this->getMock('Mage_Core_Model_Resource_Theme_Collection', array(), array(), '', false);
        $collectionFactory = $this->getMock('Mage_Core_Model_Resource_Theme_CollectionFactory',
            array('create'), array(), '', false);
        $collectionFactory->expects($this->any())->method('create')->will($this->returnValue($this->_collection));

        $this->_model = new Mage_Core_Model_Theme_Label($collectionFactory, $this->_helper);
    }

    /**
     * @dataProvider checkThemeCompatibleDataProvider
     * @covers Mage_Core_Model_Theme_Label::getLabelsCollection
     */
    public function testCheckThemeCompatible($themeData, $expected)
    {

        $collectionMock = $this->_collection;
        $collectionMock->expects($this->atLeastOnce())->method('setOrder')->with('theme_title', $this->anything());
        $collectionMock->expects($this->atLeastOnce())->method('filterVisibleThemes')->will($this->returnSelf());
        $collectionMock->expects($this->atLeastOnce())->method('addAreaFilter')->will($this->returnSelf());
        $toOptionArray = function () use ($collectionMock) {
            $result = array();
            foreach ($collectionMock as $item) {
                $result[] = array($item->getId() => $item->getThemeTitle());
            }
            return $result;
        };
        $collectionMock->expects($this->atLeastOnce())->method('toOptionArray')
            ->will($this->returnCallback($toOptionArray));

        $themes = array();
        foreach ($themeData as $theme) {
            $themeModel = $this->getMock('Mage_Core_Model_Theme', array('isThemeCompatible'), array(), '', false);
            $themeModel->expects($this->atLeastOnce())->method('isThemeCompatible')
                ->will($this->returnValue($theme['compatible']));
            /** @var $themeModel Mage_Core_Model_Theme */
            $themeModel->setId($theme['theme_id']);
            $themeModel->setThemeTitle($theme['theme_title']);
            $themes[] = $themeModel;
        }
        $this->_collection->expects($this->atLeastOnce())->method('getIterator')
            ->will($this->returnValue(new ArrayIterator($themes)));

        $this->assertEquals($expected, $this->_model->getLabelsCollection());
    }

    /**
     * @return array
     */
    public function checkThemeCompatibleDataProvider()
    {
        return array(
            array(
                'themeData' => array (
                    array(
                        'compatible'  => true,
                        'theme_id'    => 1,
                        'theme_title' => 'Title1'
                    ),
                    array(
                        'compatible'  => true,
                        'theme_id'    => 2,
                        'theme_title' => 'Title2'
                    ),
                    array(
                        'compatible'  => true,
                        'theme_id'    => 3,
                        'theme_title' => 'Title3'
                    ),
                    array(
                        'compatible'  => false,
                        'theme_id'    => 4,
                        'theme_title' => 'Title4'
                    ),
                ),
                'expected' => array(
                    array(1 => 'Title1'),
                    array(2 => 'Title2'),
                    array(3 => 'Title3'),
                    array(4 => 'Title4 (incompatible version)')
                )
            ),
        );
    }
}
