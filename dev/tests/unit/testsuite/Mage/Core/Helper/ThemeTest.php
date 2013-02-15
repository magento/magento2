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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Helper_ThemeTest extends PHPUnit_Framework_TestCase
{
    const ROOT = '/zzz';
    const APP = '/zzz/qqq';
    const MODULES = '/zzz/qqq/code00';
    const THEMES = '/zzz/qqq/design00';
    const PUB_LIB = '/zzz/qqq/js00';

    /**
     * @dataProvider getSafePathDataProvider
     * @param string $filePath
     * @param string $basePath
     * @param string $expectedResult
     */
    public function testGetSafePath($filePath, $basePath, $expectedResult)
    {
        /** @var $design Mage_Core_Model_Design_Package */
        $design = $this->getMock('Mage_Core_Model_Design_Package', null, array(), '', false);

        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = $this->getMock('Mage_Core_Model_Dir', null, array(), '', false);

        /** @var $layoutMergeFactory Mage_Core_Model_Layout_Merge_Factory */
        $layoutMergeFactory = $this->getMock('Mage_Core_Model_Layout_Merge_Factory', null, array(), '', false);

        /** @var $themeCollection Mage_Core_Model_Resource_Theme_Collection */
        $themeCollection = $this->getMock('Mage_Core_Model_Resource_Theme_Collection', null, array(), '', false);

        /** @var $translator Mage_Core_Model_Translate */
        $translator = $this->getMock('Mage_Core_Model_Translate', null, array(), '', false);

        $helper = new Mage_Core_Helper_Theme($design, $dirs, $layoutMergeFactory, $themeCollection, $translator);

        $result = $helper->getSafePath($filePath, $basePath);

        $this->assertEquals($expectedResult, $result);
    }

    public function getSafePathDataProvider()
    {
        return array(
            array('/1/2/3/4/5/6.test', '/1/2/3/', '4/5/6.test'),
            array('/1/2/3/4/5/6.test', '/1/2/3', '4/5/6.test'),
        );
    }

    /**
     * @dataProvider getCssFilesDataProvider
     * @param string $layoutStr
     * @param array $expectedResult
     */
    public function testGetCssFiles($layoutStr, $expectedResult)
    {
        // 1. Set data
        $themeId = 123;
        $themeArea = 'area123';

        // 2. Get theme model
        $theme = $this->_getTheme($themeId, $themeArea);

        // 3. Get Design Package model
        $params = array(
            'area'       => $themeArea,
            'themeModel' => $theme,
            'skipProxy'  => true
        );
        $map = array(
            array('test1.css', $params, '/zzz/qqq/test1.css'),
            array('test2.css', $params, '/zzz/qqq/test2.css'),
            array('Mage_Core::test3.css', $params, '/zzz/qqq/test3.css'),
            array('test4.css', $params, '/zzz/qqq/test4.css'),
            array('test21.css', $params, '/zzz/qqq/test21.css'),
            array('test22.css', $params, '/zzz/qqq/test22.css'),
            array('Mage_Core::test23.css', $params, '/zzz/qqq/test23.css'),
            array('test24.css', $params, '/zzz/qqq/test24.css'),
        );
        $design = $this->_getDesign($map);

        // 4. Get dirs model
        $dirs = $this->_getDirs();

        // 5. Get layout merge model and factory
        $layoutMergeFactory = $this->_getLayoutMergeFactory($layoutStr);

        /** @var $themeCollection Mage_Core_Model_Resource_Theme_Collection */
        $themeCollection = $this->getMock('Mage_Core_Model_Resource_Theme_Collection', null, array(), '', false);

        /** @var $translator Mage_Core_Model_Translate */
        $translator = $this->getMock('Mage_Core_Model_Translate', null, array(), '', false);

        // 6. Run tested method
        $helper = new Mage_Core_Helper_Theme($design, $dirs, $layoutMergeFactory, $themeCollection, $translator);
        $result = $helper->getCssFiles($theme);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCssFilesDataProvider()
    {
        return array(
            array(
                '<block type="Mage_Page_Block_Html_Head" name="head">
                    <action method="addCss"><param>test1.css</param></action>
                </block>',
                array(
                    'test1.css' => array(
                        'id'       => 'test1.css',
                        'path'     => '/zzz/qqq/test1.css',
                        'safePath' => 'qqq/test1.css'
                    )
                )
            ),
            array(
                '<block type="Mage_Page_Block_Html_Head" name="head">
                    <action method="addCss"><file>test2.css</file></action>
                </block>',
                array(
                    'test2.css' => array(
                        'id'       => 'test2.css',
                        'path'     => '/zzz/qqq/test2.css',
                        'safePath' => 'qqq/test2.css'
                    )
                )
            ),
            array(
                '<block type="Mage_Page_Block_Html_Head" name="head">
                    <action method="addCss"><param>Mage_Core::test3.css</param></action>
                </block>',
                array(
                    'Mage_Core::test3.css' => array(
                        'id'       => 'Mage_Core::test3.css',
                        'path'     => '/zzz/qqq/test3.css',
                        'safePath' => 'qqq/test3.css'
                    ),
                )
            ),
            array(
                '<block type="Mage_Page_Block_Html_Head" name="head">
                    <action method="addCssIe"><param>test4.css</param></action>
                </block>',
                array(
                    'test4.css' => array(
                        'id'       => 'test4.css',
                        'path'     => '/zzz/qqq/test4.css',
                        'safePath' => 'qqq/test4.css'
                    )
                )
            ),
            array(
                '<reference name="head"><action method="addCss"><param>test21.css</param></action></reference>',
                array(
                    'test21.css' => array(
                        'id'       => 'test21.css',
                        'path'     => '/zzz/qqq/test21.css',
                        'safePath' => 'qqq/test21.css'
                    ),
                )
            ),
            array(
                '<reference name="head"><action method="addCss"><file>test22.css</file></action></reference>',
                array(
                    'test22.css' => array(
                        'id'       => 'test22.css',
                        'path'     => '/zzz/qqq/test22.css',
                        'safePath' => 'qqq/test22.css'
                    ),
                )
            ),
            array(
                '<reference name="head">
                    <action method="addCss"><param>Mage_Core::test23.css</param></action>
                </reference>',
                array(
                    'Mage_Core::test23.css' => array(
                        'id'       => 'Mage_Core::test23.css',
                        'path'     => '/zzz/qqq/test23.css',
                        'safePath' => 'qqq/test23.css'
                    ),
                )
            ),
            array(
                '<reference name="head"><action method="addCssIe"><param>test24.css</param></action></reference>',
                array(
                    'test24.css' => array(
                        'id'       => 'test24.css',
                        'path'     => '/zzz/qqq/test24.css',
                        'safePath' => 'qqq/test24.css'
                    ),
                )
            ),
            array(
                '<block type="Some_Block_Class"><action method="addCss"><param>test31.css</param></action></block>',
                array(),

            ),
            array(
                '<block type="Some_Block_Class"><action method="addCss"><file>test32.css</file></action></block>',
                array(),
            ),
            array(
                '<block type="Some_Block_Class">
                    <action method="addCss"><param>Mage_Core::test33.css</param></action>
                </block>',
                array(),
            ),
            array(
                '<block type="Some_Block_Class"><action method="addCssIe"><param>test34.css</param></action></block>',
                array(),
            ),
            array(
                '<reference name="some_block_name">
                    <action method="addCss"><param>test41.css</param></action>
                </reference>',
                array(),
            ),
            array(
                '<reference name="some_block_name">
                    <action method="addCss"><file>test42.css</file></action>
                </reference>',
                array(),
            ),
            array(
                '<reference name="some_block_name">
                    <action method="addCss"><param>Mage_Core::test43.css</param></action>
                </reference>',
                array(),
            ),
            array(
                '<reference name="some_block_name">
                    <action method="addCssIe"><param>test44.css</param></action>
                </reference>',
                array(),
            ),
            array(
                '<block type="Mage_Page_Block_Html_Head" name="head">
                    <action method="addCss"><param>test1.css</param></action>
                    <action method="addCss"><file>test2.css</file></action>
                    <action method="addCss"><param>Mage_Core::test3.css</param></action>
                    <action method="addCssIe"><param>test4.css</param></action>
                </block>
                <reference name="head">
                    <action method="addCss"><param>test21.css</param></action>
                    <action method="addCss"><file>test22.css</file></action>
                    <action method="addCss"><param>Mage_Core::test23.css</param></action>
                    <action method="addCssIe"><param>test24.css</param></action>
                </reference>
                <block type="Some_Block_Class">
                    <action method="addCss"><param>test31.css</param></action>
                    <action method="addCss"><file>test32.css</file></action>
                    <action method="addCss"><param>Mage_Core::test33.css</param></action>
                    <action method="addCssIe"><param>test34.css</param></action>
                </block>
                <reference name="some_block_name">
                    <action method="addCss"><param>test41.css</param></action>
                    <action method="addCss"><file>test42.css</file></action>
                    <action method="addCss"><param>Mage_Core::test43.css</param></action>
                    <action method="addCssIe"><param>test44.css</param></action>
                </reference>',
                array(
                    'test21.css' => array(
                        'id'       => 'test21.css',
                        'path'     => '/zzz/qqq/test21.css',
                        'safePath' => 'qqq/test21.css'
                    ),
                    'test22.css' => array(
                        'id'       => 'test22.css',
                        'path'     => '/zzz/qqq/test22.css',
                        'safePath' => 'qqq/test22.css'
                    ),
                    'Mage_Core::test23.css' => array(
                        'id'       => 'Mage_Core::test23.css',
                        'path'     => '/zzz/qqq/test23.css',
                        'safePath' => 'qqq/test23.css'
                    ),
                    'test24.css' => array(
                        'id'       => 'test24.css',
                        'path'     => '/zzz/qqq/test24.css',
                        'safePath' => 'qqq/test24.css'
                    ),
                    'test1.css' => array(
                        'id'       => 'test1.css',
                        'path'     => '/zzz/qqq/test1.css',
                        'safePath' => 'qqq/test1.css'
                    ),
                    'test2.css' => array(
                        'id'       => 'test2.css',
                        'path'     => '/zzz/qqq/test2.css',
                        'safePath' => 'qqq/test2.css'
                    ),
                    'Mage_Core::test3.css' => array(
                        'id'       => 'Mage_Core::test3.css',
                        'path'     => '/zzz/qqq/test3.css',
                        'safePath' => 'qqq/test3.css'
                    ),
                    'test4.css' => array(
                        'id'       => 'test4.css',
                        'path'     => '/zzz/qqq/test4.css',
                        'safePath' => 'qqq/test4.css'
                    ),
                ),
            ),
        );
    }

    /**
     * depends testGetCssFiles
     * @dataProvider getGroupedCssFilesDataProvider
     * @param array $files
     * @param array $expectedResult
     */
    public function testGetGroupedCssFiles($files, $expectedResult)
    {
        $helper = $this->_getHelper($files);

        $theme = 'anything';
        $result = $helper->getGroupedCssFiles($theme);

        $this->assertEquals($expectedResult, $result);
    }

    public function getGroupedCssFilesDataProvider()
    {
        $item11 = array(
            'path'     => '/zzz/qqq/design00/area11/package11/theme11/test11.test',
            'safePath' => 'design00/area11/package11/theme11/test11.test'
        );
        $item12 = array(
            'path'     => '/zzz/qqq/design00/area12/package12/theme12/test12.test',
            'safePath' => 'design00/area12/package12/theme12/test12.test'
        );
        $item13 = array(
            'path'     => '/zzz/qqq/design00/area13/package13/theme13/test13.test',
            'safePath' => 'design00/area13/package13/theme13/test13.test'
        );

        $item21 = array(
            'path'     => '/zzz/qqq/code00/Mage_Core00/test21.test',
            'safePath' => 'code00/Mage_Core00/test21.test'
        );
        $item31 = array(
            'path'     => '/zzz/qqq/js00/some_path/test31.test',
            'safePath' => 'js00/some_path/test31.test'
        );
        $groups11 = array(
            '"11" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area11/package11/theme11/test11.test',
                    'safePath' => 'design00/area11/package11/theme11/test11.test'
                ),
            )
        );
        $groups12 = array(
            '"12" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area12/package12/theme12/test12.test',
                    'safePath' => 'design00/area12/package12/theme12/test12.test'
                ),
            )
        );
        $groups13 = array(
            '"13" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area13/package13/theme13/test13.test',
                    'safePath' => 'design00/area13/package13/theme13/test13.test'
                ),
            )
        );
        $groups1 = array(
            '"11" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area11/package11/theme11/test11.test',
                    'safePath' => 'design00/area11/package11/theme11/test11.test'
                ),
            ),
            '"12" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area12/package12/theme12/test12.test',
                    'safePath' => 'design00/area12/package12/theme12/test12.test'
                ),
            ),
            '"13" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area13/package13/theme13/test13.test',
                    'safePath' => 'design00/area13/package13/theme13/test13.test'
                ),
            )
        );
        $groups21 = array(
            'Framework files' => array(
                array(
                    'path'     => '/zzz/qqq/code00/Mage_Core00/test21.test',
                    'safePath' => 'code00/Mage_Core00/test21.test'
                ),
            )
        );
        $groups31 = array(
            'Library files' => array(
                array(
                    'path'     => '/zzz/qqq/js00/some_path/test31.test',
                    'safePath' => 'js00/some_path/test31.test'
                ),
            )
        );
        return array(
            array(array($item11), $groups11),
            array(array($item12), $groups12),
            array(array($item13), $groups13),
            array(array($item11, $item12, $item13), $groups1),
            array(array($item21), $groups21),
            array(array($item31), $groups31),
            array(
                array($item11, $item12, $item13, $item21, $item31),
                array_merge($groups1, $groups21, $groups31)
            ),
        );
    }

    /**
     * depends testGetCssFiles
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Invalid view file directory "some_path/test.test"
     */
    public function testGetGroupedCssFilesException()
    {
        $files = array(array(
            'path'     => '/zzz/some_path/test.test',
            'safePath' => 'some_path/test.test'
        ));

        $helper = $this->_getHelper($files);

        $theme = 'anything';
        $helper->getGroupedCssFiles($theme);
    }

    /**
     * @param int $themeId
     * @param string $themeArea
     * @return Mage_Core_Model_Theme|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTheme($themeId, $themeArea)
    {
        /** @var $theme Mage_Core_Model_Theme */
        $theme = $this->getMock('Mage_Core_Model_Theme',
            array('getThemeId', 'getArea', 'getThemeTitle'), array(), '', false
        );
        $theme->expects($this->any())
            ->method('getThemeId')
            ->will($this->returnValue($themeId));
        $theme->expects($this->any())
            ->method('getArea')
            ->will($this->returnValue($themeArea));
        $theme->expects($this->any())
            ->method('getThemeTitle')
            ->will($this->returnValue($themeId));

        return $theme;
    }

    /**
     * @param array $map
     * @return Mage_Core_Model_Design_Package|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDesign($map)
    {
        /** @var $design Mage_Core_Model_Design_Package */
        $design = $this->getMock('Mage_Core_Model_Design_Package', array('getViewFile'), array(), '', false);
        $design->expects($this->any())
            ->method('getViewFile')
            ->will($this->returnValueMap($map));

        return $design;
    }

    /**
     * @param string $layoutStr
     * @return Mage_Core_Model_Layout_Merge_Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getLayoutMergeFactory($layoutStr)
    {
        /** @var $layoutMerge Mage_Core_Model_Layout_Merge */
        $layoutMerge = $this->getMock('Mage_Core_Model_Layout_Merge',
            array('getFileLayoutUpdatesXml'), array(), '', false
        );
        $xml = '<layouts>' . $layoutStr . '</layouts>';
        $layoutElement = simplexml_load_string($xml);
        $layoutMerge->expects($this->any())
            ->method('getFileLayoutUpdatesXml')
            ->will($this->returnValue($layoutElement));

        /** @var $layoutMergeFactory Mage_Core_Model_Layout_Merge_Factory */
        $layoutMergeFactory = $this->getMock('Mage_Core_Model_Layout_Merge_Factory',
            array('create'), array(), '', false
        );
        $layoutMergeFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($layoutMerge));

        return $layoutMergeFactory;
    }

    /**
     * @return Mage_Core_Model_Dir|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDirs()
    {
        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = $this->getMock('Mage_Core_Model_Dir', array('getDir'), array(), '', false);
        $dirs->expects($this->any())
            ->method('getDir')
            ->will($this->returnValueMap(array(
                array(Mage_Core_Model_Dir::ROOT, self::ROOT),
                array(Mage_Core_Model_Dir::APP, self::APP),
                array(Mage_Core_Model_Dir::MODULES, self::MODULES),
                array(Mage_Core_Model_Dir::THEMES, self::THEMES),
                array(Mage_Core_Model_Dir::PUB_LIB, self::PUB_LIB),
            )));

        return $dirs;
    }

    /**
     * @return Mage_Core_Model_Resource_Theme_Collection|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getThemeCollection()
    {
        $theme11 = $this->_getTheme('11', 'area11');
        $theme12 = $this->_getTheme('12', 'area12');
        $theme13 = $this->_getTheme('13', 'area13');

        /** @var $themeCollection Mage_Core_Model_Resource_Theme_Collection */
        $themeCollection = $this->getMock('Mage_Core_Model_Resource_Theme_Collection',
            array('getThemeByFullPath'), array(), '', false
        );
        $themeCollection->expects($this->any())
            ->method('getThemeByFullPath')
            ->will($this->returnValueMap(array(
                array('area11/package11/theme11', $theme11),
                array('area12/package12/theme12', $theme12),
                array('area13/package13/theme13', $theme13),
        )));

        return $themeCollection;
    }

    /**
     * @param array $files
     * @return Mage_Core_Helper_Theme|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getHelper($files)
    {
        // Get theme collection
        $themeCollection = $this->_getThemeCollection();

        // 3. Get Design Package model
        /** @var $design Mage_Core_Model_Design_Package */
        $design = $this->getMock('Mage_Core_Model_Design_Package', null, array(), '', false);

        // 4. Get dirs model
        $dirs = $this->_getDirs();

        // 5. Get layout merge model and factory
        /** @var $layoutMergeFactory Mage_Core_Model_Layout_Merge_Factory|PHPUnit_Framework_MockObject_MockObject */
        $layoutMergeFactory = $this->getMock('Mage_Core_Model_Layout_Merge_Factory', null, array(), '', false);

        /** @var $translator Mage_Core_Model_Translate */
        $translator = $this->getMock('Mage_Core_Model_Translate', null, array(), '', false);

        /** @var $helper Mage_Core_Helper_Theme */
        $helper = $this->getMock('Mage_Core_Helper_Theme', array('getCssFiles', '__'), array(
            $design, $dirs, $layoutMergeFactory, $themeCollection, $translator
        ));
        $helper->expects($this->once())
            ->method('getCssFiles')
            ->will($this->returnValue($files));
        $helper->expects($this->any())
            ->method('__')
            ->will($this->returnCallback('sprintf'));

        return $helper;
    }
}
