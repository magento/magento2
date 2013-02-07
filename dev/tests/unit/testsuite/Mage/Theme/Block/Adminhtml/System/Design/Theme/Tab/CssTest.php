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
 * @package     Mage_Theme
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Theme_Block_Adminhtml_System_Design_Theme_Tab_CssTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css
     */
    protected $_model;

    /**
     * @var Magento_ObjectManager_Zend
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_model = $this->getMock(
            'Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css',
            array('_getCurrentTheme'),
            $this->_prepareModelArguments(),
            '',
            true
        );
    }

    /**
     * @return array
     */
    protected function _prepareModelArguments()
    {
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);

        $this->_objectManager = $this->getMock('Magento_ObjectManager_Zend', array('get'), array(), '', false);
        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = new Mage_Core_Model_Dir(__DIR__);

        $constructArguments = $objectManagerHelper->getConstructArguments(
            Magento_Test_Helper_ObjectManager::BLOCK_ENTITY,
            'Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css',
            array(
                 'objectManager'   => $this->_objectManager,
                 'dirs'            => $dirs,
                 'uploaderService' => $this->getMock('Mage_Theme_Model_Uploader_Service', array(), array(), '', false),
                 'urlBuilder'      => $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false)
            )
        );
        return $constructArguments;
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testGetUploadCssFileNote()
    {
        $method = self::getMethod('_getUploadCssFileNote');
        /** @var $sizeModel Magento_File_Size */
        $sizeModel = $this->getMock('Magento_File_Size', null, array(), '', false);

        $this->_objectManager->expects($this->any())
            ->method('get')
            ->with('Magento_File_Size')
            ->will($this->returnValue($sizeModel));

        $result = $method->invokeArgs($this->_model, array());
        $expectedResult = 'Allowed file types *.css.<br />';
        $expectedResult .= 'The file you upload will replace the existing custom.css file (shown below).<br />';
        $expectedResult .= sprintf(
            'Max file size to upload %sM',
            $sizeModel->getMaxFileSizeInMb()
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetAdditionalElementTypes()
    {
        $method = self::getMethod('_getAdditionalElementTypes');

        /** @var $configModel Mage_Core_Model_Config */
        $configModel = $this->getMock('Mage_Core_Model_Config', null, array(), '', false);

        $this->_objectManager->expects($this->any())
            ->method('get')
            ->with('Mage_Core_Model_Config')
            ->will($this->returnValue($configModel));

        $result = $method->invokeArgs($this->_model, array());
        $expectedResult = array(
            'links' => 'Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Form_Element_Links',
            'css_file' => 'Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Form_Element_File'
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param array $files
     * @param array $expectedResult
     * @dataProvider getGroupedFilesProvider
     */
    public function testGetGroupedFiles($files, $expectedResult)
    {
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('getThemeTitle', 'getId'), array(), '', false);
        $themeMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $themeMock->expects($this->any())->method('getThemeTitle')->will($this->returnValue('test title'));

        $helperFactoryMock = $this->getMock(
            'Mage_Core_Model_Factory_Helper', array('get', 'urlEncode'), array(), '', false
        );
        $helperFactoryMock->expects($this->any())->method('get')->with($this->equalTo('Mage_Theme_Helper_Data'))
            ->will($this->returnSelf());

        $helperFactoryMock->expects($this->any())->method('urlEncode')->will($this->returnArgument(0));

        $constructArguments = $this->_prepareModelArguments();
        $constructArguments['helperFactory'] = $helperFactoryMock;
        $constructArguments['objectManager'] = $objectManagerMock = $this->getMockBuilder('Magento_ObjectManager')
            ->setMethods(array('create', 'get'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $collectionMock = $this->getMock(
            'Mage_Core_Model_Resource_Theme_Collection',
            get_class_methods('Mage_Core_Model_Resource_Theme_Collection'),
            array(),
            '',
            false
        );

        $collectionMock->expects($this->any())->method('getThemeByFullPath')->will($this->returnValue($themeMock));

        $configMock = $this->getMock('Mage_Core_Model_Config', get_class_methods('Mage_Core_Model_Config'),
            array(), '', false);

        $objectManagerMock->expects($this->any())->method('create')
            ->with($this->equalTo('Mage_Core_Model_Resource_Theme_Collection'))
            ->will($this->returnValue($collectionMock));

        $objectManagerMock->expects($this->any())->method('get')->with($this->equalTo('Mage_Core_Model_Config'))
            ->will($this->returnValue($configMock));

        $this->_model = $this->getMock('Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css',
            array('getUrl', '_getCurrentTheme'), $constructArguments, '', true);

        $this->_model->setFiles($files);
        $this->_model->expects($this->any())->method('_getCurrentTheme')->will($this->returnValue($themeMock));
        $this->_model->expects($this->any())->method('getUrl')->will($this->returnArgument(1));

        $method = self::getMethod('_getGroupedFiles');
        $result = $method->invokeArgs($this->_model, array());
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getGroupedFilesProvider()
    {
        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = new Mage_Core_Model_Dir(__DIR__);

        $designDir = str_replace(
            $dirs->getDir(Mage_Core_Model_Dir::APP), '', $dirs->getDir(Mage_Core_Model_Dir::THEMES)
        );
        $jsDir = str_replace($dirs->getDir(Mage_Core_Model_Dir::APP), '', $dirs->getDir(Mage_Core_Model_Dir::PUB_LIB));
        $codeDir = str_replace(
            $dirs->getDir(Mage_Core_Model_Dir::APP), '', $dirs->getDir(Mage_Core_Model_Dir::MODULES)
        );
        return array(
            array(array(), array()),
            array(
                array('mage/calendar.css' => str_replace('/', DIRECTORY_SEPARATOR,
                    $dirs->getDir(Mage_Core_Model_Dir::MODULES) . '/pub/lib/mage/calendar.css')),
                array('Framework files' => array(
                    array(
                        'href' => array('theme_id' => 1, 'file' => 'mage/calendar.css'),
                        'label' => 'mage/calendar.css',
                        'title' => str_replace('/', DIRECTORY_SEPARATOR, $codeDir . '/pub/lib/mage/calendar.css'),
                        'delimiter' => '<br />'
            )))),
            array(
                array('Mage_Page::css/tabs.css' => str_replace('/', DIRECTORY_SEPARATOR,
                    $dirs->getDir(Mage_Core_Model_Dir::MODULES) . '/core/Mage/Page/view/frontend/css/tabs.css')),
                array('Framework files' => array(
                    array(
                        'href' => array('theme_id' => 1, 'file' => 'Mage_Page::css/tabs.css'),
                        'label' => 'Mage_Page::css/tabs.css',
                        'title' => str_replace('/', DIRECTORY_SEPARATOR,
                            $codeDir . '/core/Mage/Page/view/frontend/css/tabs.css'),
                        'delimiter' => '<br />'
            )))),
            array(
                array('mage/calendar.css' => str_replace('/', DIRECTORY_SEPARATOR,
                    $dirs->getDir(Mage_Core_Model_Dir::PUB_LIB) . '/mage/calendar.css')),
                array('Library files' => array(
                    array(
                        'href' => array('theme_id' => 1, 'file' => 'mage/calendar.css'),
                        'label' => 'mage/calendar.css',
                        'title' => str_replace('/', DIRECTORY_SEPARATOR, $jsDir . '/mage/calendar.css'),
                        'delimiter' => '<br />'
            )))),
            array(
                array('mage/calendar.css' => str_replace('/', DIRECTORY_SEPARATOR,
                    $dirs->getDir(Mage_Core_Model_Dir::THEMES) . '/frontend/default/demo/css/styles.css'),
                ),
                array('"test title" Theme files' => array(
                    array(
                        'href' => array('theme_id' => 1, 'file' => 'mage/calendar.css'),
                        'label' => 'mage/calendar.css',
                        'title' => str_replace('/', DIRECTORY_SEPARATOR,
                            $designDir . '/frontend/default/demo/css/styles.css'),
                        'delimiter' => '<br />'
            )))),
        );
    }

    /**
     * @dataProvider sortGroupFilesCallbackProvider
     */
    public function testSortGroupFilesCallback($firstGroup, $secondGroup, $expectedResult)
    {
        $method = self::getMethod('_sortGroupFilesCallback');
        $result = $method->invokeArgs($this->_model, array($firstGroup, $secondGroup));
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function sortGroupFilesCallbackProvider()
    {
        return array(
            array(
                array('label' => 'abcd'),
                array('label' => 'abc'),
                1
            ),
            array(
                array('label' => 'abc'),
                array('label' => 'abcd'),
                -1
            ),
            array(
                array('label' => 'abc'),
                array('label' => 'abc'),
                0
            ),
            array(
                array('label' => 'Mage_Core::abc'),
                array('label' => 'abc'),
                1
            ),
            array(
                array('label' => 'abc'),
                array('label' => 'Mage_Core::abc'),
                -1
            ),
            array(
                array('label' => 'Mage_Core::abc'),
                array('label' => 'Mage_Core::abcd'),
                -1
            ),
            array(
                array('label' => 'Mage_Core::abcd'),
                array('label' => 'Mage_Core::abc'),
                1
            ),
            array(
                array('label' => 'Mage_Core::abc'),
                array('label' => 'Mage_Core::abc'),
                0
            ),
        );
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Invalid view file directory "xyz"
     */
    public function testGetGroupException()
    {
        $method = self::getMethod('_getGroup');
        $method->invokeArgs($this->_model, array('xyz'));
    }

    /**
     * @param string $filename
     * @param string $filePathForSearch
     * @param int|string $themeId
     * @dataProvider getGroupProvider
     */
    public function testGetGroup($filename, $filePathForSearch, $themeId)
    {
        $constructArguments = $this->_prepareModelArguments();
        $constructArguments['objectManager'] = $objectManagerMock = $this->getMockBuilder('Magento_ObjectManager')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $configMock = $this->getMock('Mage_Core_Model_Config', get_class_methods('Mage_Core_Model_Config'),
            array(), '', false);

        $objectManagerMock->expects($this->any())->method('get')->with($this->equalTo('Mage_Core_Model_Config'))
            ->will($this->returnValue($configMock));

        $this->_model = $this->getMock(
            'Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css',
            array('_getThemeByFilename'),
            $constructArguments,
            '',
            true
        );

        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('getThemeId'), array(), '', false);
        $themeMock->expects($this->any())
            ->method('getThemeId')
            ->will($this->returnValue($themeId));

        $this->_model->expects($this->any())
            ->method('_getThemeByFilename')
            ->with($filePathForSearch)
            ->will($this->returnValue($themeMock));

        $method = self::getMethod('_getGroup');
        $result = $method->invokeArgs($this->_model, array($filename));

        $this->assertCount(2, $result);

        if ($filePathForSearch) {
            $this->assertSame($themeMock, $result[1]);
            $this->assertEquals(array($themeId, $themeMock), $result);
        } else {
            $this->assertEquals(array($themeId, null), $result);
        }
    }

    /**
     * @return array
     */
    public function getGroupProvider()
    {
        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = new Mage_Core_Model_Dir(__DIR__);

        $designDir = $dirs->getDir(Mage_Core_Model_Dir::THEMES);
        $jsDir = $dirs->getDir(Mage_Core_Model_Dir::PUB_LIB);
        $codeDir = $dirs->getDir(Mage_Core_Model_Dir::MODULES);

        return array(
            array(
                $designDir . str_replace('/', DIRECTORY_SEPARATOR, '/a/b/c/f/file.xml'),
                str_replace('/', DIRECTORY_SEPARATOR, '/a/b/c/f/file.xml'),
                1
            ),
            array(
                $jsDir . str_replace('/', DIRECTORY_SEPARATOR, '/a/b/c/f/file.xml'),
                null,
                $jsDir
            ),
            array(
                $codeDir . str_replace('/', DIRECTORY_SEPARATOR, '/a/b/c/f/file.xml'),
                null,
                $codeDir
            ),
        );
    }

    /**
     * @dataProvider sortThemesByHierarchyCallbackProvider
     */
    public function testSortThemesByHierarchyCallback($firstThemeParentId, $parentOfParentTheme,
        $secondThemeId, $expectedResult
    ) {
        list($firstTheme, $secondTheme) = $this->_prepareThemesForHierarchyCallback(
            $firstThemeParentId, $parentOfParentTheme, $secondThemeId
        );

        $method = self::getMethod('_sortThemesByHierarchyCallback');
        $result = $method->invokeArgs($this->_model, array($firstTheme, $secondTheme));
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function sortThemesByHierarchyCallbackProvider()
    {
        return array(
            array(1, null, 1, -1),
            array(1, $this->_getThemeMockFroHierarchyCallback(), 2, -1),
            array(1, null, 2, 1),
        );
    }

    /**
     * @param int $firstThemeParentId
     * @param Mage_Core_Model_Theme|null $parentOfParentTheme
     * @param int $secondThemeId
     * @return array
     */
    protected function _prepareThemesForHierarchyCallback($firstThemeParentId, $parentOfParentTheme, $secondThemeId)
    {
        $parentTheme = $this->getMock('Mage_Core_Model_Theme', array('getParentTheme', 'getId'), array(), '', false);

        $firstTheme = $this->getMock('Mage_Core_Model_Theme', array('getParentTheme', 'getId'), array(), '', false);
        $firstTheme->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentTheme));

        $firstTheme->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(999));

        $parentTheme->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($firstThemeParentId));

        $parentTheme->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentOfParentTheme));

        $secondTheme = $this->getMock('Mage_Core_Model_Theme', array('getId'), array(), '', false);
        $secondTheme->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($secondThemeId));
        return array($firstTheme, $secondTheme);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getThemeMockFroHierarchyCallback()
    {
        $parentOfParentTheme = $this->getMock('Mage_Core_Model_Theme', array('getId', 'getParentTheme'),
            array(), '', false);
        $parentOfParentTheme->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));
        $parentOfParentTheme->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue(false));

        $parentTheme = $this->getMock('Mage_Core_Model_Theme', array('getParentTheme'), array(), '', false);
        $parentTheme->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentOfParentTheme));
        return $parentTheme;
    }

    /**
     * @param string $fileName
     * @param string $expectedResult
     * @dataProvider getThemeByFilenameProvider
     */
    public function testGetThemeByFilename($fileName, $expectedResult)
    {
        $constructArguments = $this->_prepareModelArguments();

        $constructArguments['objectManager'] = $objectManagerMock = $this->getMockBuilder('Magento_ObjectManager')
            ->setMethods(array('create'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $collectionMock = $this->getMock('Mage_Core_Model_Resource_Theme_Collection',
            get_class_methods('Mage_Core_Model_Resource_Theme_Collection'), array(), '', false);

        $collectionMock->expects($this->atLeastOnce())
            ->method('getThemeByFullPath')
            ->will($this->returnArgument(0));

        $objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->with($this->equalTo('Mage_Core_Model_Resource_Theme_Collection'))
            ->will($this->returnValue($collectionMock));

        $this->_model = $this->getMock(
            'Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css', array(), $constructArguments, '', true
        );

        $method = self::getMethod('_getThemeByFilename');
        $result = $method->invokeArgs($this->_model, array(str_replace('/', DIRECTORY_SEPARATOR, $fileName)));
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getThemeByFilenameProvider()
    {
        return array(array('a/b/c/d/e.xml', 'a/b/c'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Theme path does not recognized
     */
    public function testGetThemeByFilenameException()
    {
        $method = self::getMethod('_getThemeByFilename');
        $method->invokeArgs($this->_model, array('a'));
    }

    public function testGetGroupLabels()
    {
        $themeModel = $this->getMock('Mage_Core_Model_Theme', array('getThemeId', 'getThemeTitle'), array(), '', false);
        $themeModel->expects($this->any())
            ->method('getThemeId')
            ->will($this->returnValue(1));

        $themeModel->expects($this->any())
            ->method('getThemeTitle')
            ->will($this->returnValue('title'));

        $method = self::getMethod('_getGroupLabels');
        $result = $method->invokeArgs($this->_model, array(array($themeModel)));

        $this->assertContains('Library files', $result);
        $this->assertContains('Framework files', $result);
        $this->assertContains('"title" Theme files', $result);
        $this->assertArrayHasKey(1, $result);
    }

    /**
     * @param array $groups
     * @param array $order
     * @param array $expectedResult
     * @dataProvider sortArrayByArrayProvider
     */
    public function testSortArrayByArray($groups, $order, $expectedResult)
    {
        $method = self::getMethod('_sortArrayByArray');
        $result = $method->invokeArgs($this->_model, array($groups, $order));
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function sortArrayByArrayProvider()
    {
        return array(
            array(
                array('b' => 'item2', 'a' => 'item1', 'c' => 'item3'),
                array('a', 'b', 'c'),
                array('a' => 'item1', 'b' => 'item2', 'c' => 'item3')
            ),
            array(
                array('x' => 'itemX'),
                array('a', 'b', 'c'),
                array('x' => 'itemX')
            ),
            array(
                array('b' => 'item2', 'a' => 'item1', 'c' => 'item3', 'd' => 'item4', 'e' => 'item5'),
                array('d', 'e'),
                array('d' => 'item4', 'e' => 'item5', 'b' => 'item2', 'a' => 'item1', 'c' => 'item3'),
            ),
        );
    }

    public function testGetTabLabel()
    {
        $this->assertEquals('CSS Editor', $this->_model->getTabLabel());
    }

    /**
     * @param string $name
     * @return ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
