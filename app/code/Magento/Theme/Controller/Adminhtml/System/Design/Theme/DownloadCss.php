<?php
/**
 *
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
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use \Magento\Framework\App\ResponseInterface;

class DownloadCss extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Download css file
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        $file = $this->getRequest()->getParam('file');

        /** @var $helper \Magento\Core\Helper\Theme */
        $helper = $this->_objectManager->get('Magento\Core\Helper\Theme');
        $fileId = $helper->urlDecode($file);
        try {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface')->load($themeId);
            if (!$theme->getId()) {
                throw new \InvalidArgumentException(sprintf('Theme not found: "%1".', $themeId));
            }
            $asset = $this->_assetRepo->createAsset($fileId, array('themeModel' => $theme));
            $relPath = $this->_appFileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem::ROOT_DIR)
                ->getRelativePath($asset->getSourceFile());
            return $this->_fileFactory->create(
                $relPath,
                array(
                    'type'  => 'filename',
                    'value' => $relPath
                ),
                \Magento\Framework\App\Filesystem::ROOT_DIR
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('File not found: "%1".', $fileId));
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }
}
