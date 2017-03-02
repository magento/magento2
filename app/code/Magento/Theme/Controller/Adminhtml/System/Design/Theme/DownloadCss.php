<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

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

        /** @var $urlDecoder \Magento\Framework\Url\DecoderInterface */
        $urlDecoder = $this->_objectManager->get(\Magento\Framework\Url\DecoderInterface::class);
        $fileId = $urlDecoder->decode($file);
        try {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            $theme = $this->_objectManager->create(
                \Magento\Framework\View\Design\ThemeInterface::class
            )->load($themeId);
            if (!$theme->getId()) {
                throw new \InvalidArgumentException(sprintf('Theme not found: "%1".', $themeId));
            }
            $asset = $this->_assetRepo->createAsset($fileId, ['themeModel' => $theme]);
            $relPath = $this->_appFileSystem->getDirectoryRead(DirectoryList::ROOT)
                ->getRelativePath($asset->getSourceFile());
            return $this->_fileFactory->create(
                $relPath,
                [
                    'type'  => 'filename',
                    'value' => $relPath
                ],
                DirectoryList::ROOT
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('File not found: "%1".', $fileId));
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }
}
