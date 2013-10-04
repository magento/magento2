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
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test of theme customization model
 */
namespace Magento\Core\Model\Theme;

class CustomizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $_modelBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customizationPath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_theme;

    protected function setUp()
    {
        $this->_fileCollection = $this->getMock('Magento\Core\Model\Resource\Theme\File\Collection',
            array(), array(), '', false);
        $collectionFactory = $this->getMock('Magento\Core\Model\Resource\Theme\File\CollectionFactory',
            array('create'), array(), '', false);
        $collectionFactory->expects($this->any())->method('create')->will($this->returnValue($this->_fileCollection));
        $this->_customizationPath = $this->getMock('Magento\Core\Model\Theme\Customization\Path',
            array(), array(), '', false);
        $this->_theme = $this->getMock('Magento\Core\Model\Theme', array('save', 'load'), array(), '', false);

        $this->_modelBuilder = $this->getMockBuilder('Magento\Core\Model\Theme\Customization')
            ->setConstructorArgs(array($collectionFactory, $this->_customizationPath, $this->_theme))
            ->setMethods(null);
    }

    protected function tearDown()
    {
        $this->_modelBuilder = null;
        $this->_fileCollection = null;
        $this->_customizationPath = null;
        $this->_theme = null;
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization::getFiles
     * @covers \Magento\Core\Model\Theme\Customization::__construct
     */
    public function testGetFiles()
    {
        $this->_fileCollection->expects($this->once())->method('addThemeFilter')->with($this->_theme)
            ->will($this->returnSelf());
        $this->_fileCollection->expects($this->once())->method('setDefaultOrder')->will($this->returnSelf());
        $this->_fileCollection->expects($this->once())->method('getItems')->will($this->returnValue(array()));
        $this->assertEquals(array(), $this->_modelBuilder->getMock()->getFiles());
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization::getFilesByType
     */
    public function testGetFilesByType()
    {
        $this->_fileCollection->expects($this->once())->method('addThemeFilter')->with($this->_theme)
            ->will($this->returnSelf());
        $this->_fileCollection->expects($this->once())->method('setDefaultOrder')->will($this->returnSelf());
        $this->_fileCollection->expects($this->once())->method('addFieldToFilter')->with('file_type', 'sample-type')
            ->will($this->returnSelf());
        $this->_fileCollection->expects($this->once())->method('getItems')->will($this->returnValue(array()));
        $this->assertEquals(array(), $this->_modelBuilder->getMock()->getFilesByType('sample-type'));
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization::generateFileInfo
     */
    public function testGenerationOfFileInfo()
    {
        $file = $this->getMock('Magento\Core\Model\Theme\File', array('getFileInfo'), array(), '', false);
        $file->expects($this->once())->method('getFileInfo')->will($this->returnValue(array('sample-generation')));
        $this->assertEquals(
            array(array('sample-generation')),
            $this->_modelBuilder->getMock()->generateFileInfo(array($file))
        );
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization::getCustomizationPath
     */
    public function testGetCustomizationPath()
    {
        $this->_customizationPath->expects($this->once())->method('getCustomizationPath')->with($this->_theme)
            ->will($this->returnValue('path'));
        $this->assertEquals('path', $this->_modelBuilder->getMock()->getCustomizationPath());
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization::getThemeFilesPath
     * @dataProvider getThemeFilesPathDataProvider
     * @param string $type
     * @param string $expectedMethod
     */
    public function testGetThemeFilesPath($type, $expectedMethod)
    {
        $this->_theme->setData(array(
            'id'         => 123,
            'type'       => $type,
            'area'       => 'area51',
            'theme_path' => 'theme_path'
        ));
        $this->_customizationPath->expects($this->once())->method($expectedMethod)->with($this->_theme)
            ->will($this->returnValue('path'));
        $this->assertEquals('path', $this->_modelBuilder->getMock()->getThemeFilesPath());
    }

    /**
     * @return array
     */
    public function getThemeFilesPathDataProvider()
    {
        return array(
            'physical' => array(\Magento\Core\Model\Theme::TYPE_PHYSICAL, 'getThemeFilesPath'),
            'virtual'  => array(\Magento\Core\Model\Theme::TYPE_VIRTUAL, 'getCustomizationPath'),
            'staging'  => array(\Magento\Core\Model\Theme::TYPE_STAGING, 'getCustomizationPath'),
        );
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization::getCustomViewConfigPath
     */
    public function testGetCustomViewConfigPath()
    {
        $this->_customizationPath->expects($this->once())->method('getCustomViewConfigPath')->with($this->_theme)
            ->will($this->returnValue('path'));
        $this->assertEquals('path', $this->_modelBuilder->getMock()->getCustomViewConfigPath());
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization::reorder
     * @dataProvider customFileContent
     */
    public function testReorder($sequence, $filesContent)
    {
        $files = array();
        foreach ($filesContent as $fileContent) {
            $file = $this->getMock('Magento\Core\Model\Theme\File', array('save'), array(), '', false);
            $file->expects($fileContent['isCalled'])->method('save')->will($this->returnSelf());
            $file->setData($fileContent['content']);
            $files[] = $file;
        }
        $model = $this->_modelBuilder->setMethods(array('getFilesByType'))->getMock();
        $model->expects($this->once())->method('getFilesByType')->with('sample-type')->will($this->returnValue($files));
        $model->reorder('sample-type', $sequence);
    }

    /**
     * Reorder test content
     *
     * @return array
     */
    public function customFileContent()
    {
        return array(array(
            'sequence'     => array(3, 2, 1),
            'filesContent' => array(
                array(
                    'isCalled' => $this->once(),
                    'content'  => array(
                        'id'         => 1,
                        'theme_id'   => 123,
                        'file_path'  => 'css/custom_file1.css',
                        'content'    => 'css content',
                        'sort_order' => 1
                    )
                ),
                array(
                    'isCalled' => $this->never(),
                    'content'  => array(
                        'id'         => 2,
                        'theme_id'   => 123,
                        'file_path'  => 'css/custom_file2.css',
                        'content'    => 'css content',
                        'sort_order' => 1
                    )
                ),
                array(
                    'isCalled' => $this->once(),
                    'content'  => array(
                        'id'         => 3,
                        'theme_id'   => 123,
                        'file_path'  => 'css/custom_file3.css',
                        'content'    => 'css content',
                        'sort_order' => 5
                    )
                )
            )
        ));
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization::delete
     */
    public function testDelete()
    {
        $file = $this->getMock('Magento\Core\Model\Theme\File', array('delete'), array(), '', false);
        $file->expects($this->once())->method('delete')->will($this->returnSelf());
        $file->setData(array(
            'id'         => 1,
            'theme_id'   => 123,
            'file_path'  => 'css/custom_file1.css',
            'content'    => 'css content',
            'sort_order' => 1
        ));

        $model = $this->_modelBuilder->setMethods(array('getFiles'))->getMock();
        $model->expects($this->once())->method('getFiles')->will($this->returnValue(array($file)));
        $model->delete(array(1));
    }
}
