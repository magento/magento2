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

/**
 * Test of theme customization model
 */
namespace Magento\Framework\View\Design\Theme;

class CustomizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Customization
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customizationPath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $theme;

    protected function setUp()
    {
        $this->fileProvider = $this->getMock(
            'Magento\Framework\View\Design\Theme\FileProviderInterface',
            array(),
            array(),
            '',
            false
        );
        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\File\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $collectionFactory->expects($this->any())->method('create')->will($this->returnValue($this->fileProvider));
        $this->customizationPath = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization\Path',
            array(),
            array(),
            '',
            false
        );
        $this->theme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('__wakeup', 'save', 'load'),
            array(),
            '',
            false
        );

        $this->model = new Customization($this->fileProvider, $this->customizationPath, $this->theme);
    }

    protected function tearDown()
    {
        $this->model = null;
        $this->fileProvider = null;
        $this->customizationPath = null;
        $this->theme = null;
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getFiles
     * @covers \Magento\Framework\View\Design\Theme\Customization::__construct
     */
    public function testGetFiles()
    {
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme
        )->will(
            $this->returnValue(array())
        );
        $this->assertEquals(array(), $this->model->getFiles());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getFilesByType
     */
    public function testGetFilesByType()
    {
        $type = 'sample-type';
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme,
            array('file_type' => $type)
        )->will(
            $this->returnValue(array())
        );
        $this->assertEquals(array(), $this->model->getFilesByType($type));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::generateFileInfo
     */
    public function testGenerationOfFileInfo()
    {
        $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'getFileInfo'), array(), '', false);
        $file->expects($this->once())->method('getFileInfo')->will($this->returnValue(array('sample-generation')));
        $this->assertEquals(array(array('sample-generation')), $this->model->generateFileInfo(array($file)));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getCustomizationPath
     */
    public function testGetCustomizationPath()
    {
        $this->customizationPath->expects(
            $this->once()
        )->method(
            'getCustomizationPath'
        )->with(
            $this->theme
        )->will(
            $this->returnValue('path')
        );
        $this->assertEquals('path', $this->model->getCustomizationPath());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getThemeFilesPath
     * @dataProvider getThemeFilesPathDataProvider
     * @param string $type
     * @param string $expectedMethod
     */
    public function testGetThemeFilesPath($type, $expectedMethod)
    {
        $this->theme->setData(array('id' => 123, 'type' => $type, 'area' => 'area51', 'theme_path' => 'theme_path'));
        $this->customizationPath->expects(
            $this->once()
        )->method(
            $expectedMethod
        )->with(
            $this->theme
        )->will(
            $this->returnValue('path')
        );
        $this->assertEquals('path', $this->model->getThemeFilesPath());
    }

    /**
     * @return array
     */
    public function getThemeFilesPathDataProvider()
    {
        return array(
            'physical' => array(\Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL, 'getThemeFilesPath'),
            'virtual' => array(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL, 'getCustomizationPath'),
            'staging' => array(\Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING, 'getCustomizationPath')
        );
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getCustomViewConfigPath
     */
    public function testGetCustomViewConfigPath()
    {
        $this->customizationPath->expects(
            $this->once()
        )->method(
            'getCustomViewConfigPath'
        )->with(
            $this->theme
        )->will(
            $this->returnValue('path')
        );
        $this->assertEquals('path', $this->model->getCustomViewConfigPath());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::reorder
     * @dataProvider customFileContent
     */
    public function testReorder($sequence, $filesContent)
    {
        $files = array();
        $type = 'sample-type';
        foreach ($filesContent as $fileContent) {
            $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'save'), array(), '', false);
            $file->expects($fileContent['isCalled'])->method('save')->will($this->returnSelf());
            $file->setData($fileContent['content']);
            $files[] = $file;
        }
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme,
            array('file_type' => $type)
        )->will(
            $this->returnValue($files)
        );
        $this->assertInstanceOf(
            'Magento\Framework\View\Design\Theme\CustomizationInterface',
            $this->model->reorder($type, $sequence)
        );
    }

    /**
     * Reorder test content
     *
     * @return array
     */
    public function customFileContent()
    {
        return array(
            array(
                'sequence' => array(3, 2, 1),
                'filesContent' => array(
                    array(
                        'isCalled' => $this->once(),
                        'content' => array(
                            'id' => 1,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file1.css',
                            'content' => 'css content',
                            'sort_order' => 1
                        )
                    ),
                    array(
                        'isCalled' => $this->never(),
                        'content' => array(
                            'id' => 2,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file2.css',
                            'content' => 'css content',
                            'sort_order' => 1
                        )
                    ),
                    array(
                        'isCalled' => $this->once(),
                        'content' => array(
                            'id' => 3,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file3.css',
                            'content' => 'css content',
                            'sort_order' => 5
                        )
                    )
                )
            )
        );
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::delete
     */
    public function testDelete()
    {
        $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'delete'), array(), '', false);
        $file->expects($this->once())->method('delete')->will($this->returnSelf());
        $file->setData(
            array(
                'id' => 1,
                'theme_id' => 123,
                'file_path' => 'css/custom_file1.css',
                'content' => 'css content',
                'sort_order' => 1
            )
        );
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme
        )->will(
            $this->returnValue(array($file))
        );

        $this->assertInstanceOf(
            'Magento\Framework\View\Design\Theme\CustomizationInterface',
            $this->model->delete(array(1))
        );
    }
}
