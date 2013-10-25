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
 * @package     Magento_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Storage model test
 */
namespace Magento\Theme\Model\Wysiwyg;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Theme\Helper\Storage
     */
    protected $_helperStorage;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Theme\Model\Wysiwyg\Storage
     */
    protected $_storageModel;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_filesystem = $this->_objectManager->get('Magento\Filesystem');
        $this->_filesystem->setIsAllowCreateDirectories(true);

        /** @var $theme \Magento\View\Design\ThemeInterface */
        $theme = $this->_objectManager->create('Magento\View\Design\ThemeInterface')->getCollection()->getFirstItem();

        /** @var $request \Magento\App\Request\Http */
        $request = $this->_objectManager->get('Magento\App\Request\Http');
        $request->setParam(\Magento\Theme\Helper\Storage::PARAM_THEME_ID, $theme->getId());
        $request->setParam(\Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE,
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE);

        $this->_helperStorage = $this->_objectManager->get('Magento\Theme\Helper\Storage');

        $this->_storageModel = $this->_objectManager->create('Magento\Theme\Model\Wysiwyg\Storage', array(
            'helper' => $this->_helperStorage
        ));
    }

    protected function tearDown()
    {
        $this->_filesystem->delete($this->_helperStorage->getStorageRoot());
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::_createThumbnail
     */
    public function testCreateThumbnail()
    {
        $image = 'some_image.jpg';
        $imagePath = realpath(__DIR__) . "/_files/theme/image/{$image}";
        $tmpImagePath = $this->_copyFileToTmpCustomizationPath($imagePath);

        $method = $this->_getMethod('_createThumbnail');
        $result = $method->invokeArgs($this->_storageModel, array($tmpImagePath));

        $expectedResult = $this->_helperStorage->getThumbnailDirectory($tmpImagePath)
            . \Magento\Filesystem::DIRECTORY_SEPARATOR . $image;

        $this->assertEquals($expectedResult, $result);
        $this->assertFileExists($result);
    }

    /**
     * @param string $name
     * @return \ReflectionMethod
     */
    protected function _getMethod($name)
    {
        $class = new \ReflectionClass('Magento\Theme\Model\Wysiwyg\Storage');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Copy file to tmp theme customization path
     *
     * @param string $sourceFile
     * @return string
     */
    protected function _copyFileToTmpCustomizationPath($sourceFile)
    {
        $targetFile = $this->_helperStorage->getStorageRoot()
            . \Magento\Filesystem::DIRECTORY_SEPARATOR
            . basename($sourceFile);

        $this->_filesystem->ensureDirectoryExists(pathinfo($targetFile, PATHINFO_DIRNAME));
        $this->_filesystem->copy($sourceFile, $targetFile);
        return $targetFile;
    }
}
