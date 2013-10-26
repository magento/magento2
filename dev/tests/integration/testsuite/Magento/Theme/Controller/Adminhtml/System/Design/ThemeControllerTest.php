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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Theme\Controller\Adminhtml\System\Design;

/**
 * @magentoAppArea adminhtml
 */
class ThemeControllerTest extends \Magento\Backend\Utility\Controller
{
    /** @var \Magento\Filesystem */
    protected $_filesystem;

    /** @var \Magento\App\Dir */
    protected $_dirs;

    protected function setUp()
    {
        parent::setUp();

        $this->_filesystem = $this->_objectManager->get('Magento\Filesystem');
        $this->_dirs = $this->_objectManager->get('Magento\App\Dir');
    }

    /**
     * Test upload JS file
     */
    public function testUploadJsAction()
    {
        $_FILES = array(
            'js_files_uploader' => array(
                'name' => 'simple-js-file.js',
                'type' => 'application/x-javascript',
                'tmp_name' => $this->_prepareFileForUploading(),
                'error' => '0',
                'size' => '28'
            )
        );

        $theme = $this->_objectManager->create('Magento\View\Design\ThemeInterface')->getCollection()->getFirstItem();

        $this->getRequest()->setPost('id', $theme->getId());
        $this->dispatch('backend/admin/system_design_theme/uploadjs');
        $output = $this->getResponse()->getBody();
        $this->assertContains('"error":false', $output);
        $this->assertContains('simple-js-file.js', $output);
    }

    /**
     * Prepare file for uploading
     *
     * @return string
     */
    protected function _prepareFileForUploading()
    {
        /**
         * Copy file to writable directory.
         * Uploader can copy(upload) and then remove this temporary file.
         */
        $fileName = implode(DIRECTORY_SEPARATOR, array(__DIR__, '_files', 'simple-js-file.js'));
        $varDir = $this->_dirs->getDir(\Magento\App\Dir::VAR_DIR);
        $destinationFilePath = $varDir . DIRECTORY_SEPARATOR . 'simple-js-file.js';

        $this->_filesystem->copy($fileName, $destinationFilePath);
        $this->_filesystem->has($destinationFilePath);

        return $destinationFilePath;
    }
}
