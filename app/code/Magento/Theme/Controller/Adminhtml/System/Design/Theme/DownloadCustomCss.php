<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * The admin area controller to download custom css.
 *
 * @deprecated 100.2.0
 */
class DownloadCustomCss extends Theme implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Download custom css file
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        try {
            /** @var $themeFactory \Magento\Framework\View\Design\Theme\FlyweightFactory */
            $themeFactory = $this->_objectManager->create(\Magento\Framework\View\Design\Theme\FlyweightFactory::class);
            $theme = $themeFactory->create($themeId);
            if ($theme === null || !$theme->getId()) {
                throw new \InvalidArgumentException(__(
                    'We cannot find a theme with id "%1".',
                    $themeId
                )->render());
            }

            $customCssFiles = $theme->getCustomization()->getFilesByType(
                \Magento\Theme\Model\Theme\Customization\File\CustomCss::TYPE
            );
            /** @var $customCssFile \Magento\Framework\View\Design\Theme\FileInterface */
            $customCssFile = reset($customCssFiles);
            if ($customCssFile && $customCssFile->getContent()) {
                return $this->_fileFactory->create(
                    $customCssFile->getFileName(),
                    ['type' => 'filename', 'value' => $customCssFile->getFullPath()],
                    DirectoryList::ROOT
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t find file.')->render()
            );
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }
}
