<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Design\Theme\Customization\File\Js;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme;
use Magento\Theme\Model\Theme as ModelTheme;
use Magento\Theme\Model\Theme\Customization\File\CustomCss;
use Magento\Theme\Model\Theme\SingleFile;
use Psr\Log\LoggerInterface;

/**
 * Class Save use to save Theme data
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @deprecated 100.2.0
 */
class Save extends Theme
{
    /**
     * Save action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $redirectBack = (bool)$this->getRequest()->getParam('back', false);
        $themeData = $this->getRequest()->getParam('theme');
        $customCssData = $this->getRequest()->getParam('custom_css_content');
        $removeJsFiles = (array)$this->getRequest()->getParam('js_removed_files');
        $reorderJsFiles = array_keys($this->getRequest()->getParam('js_order', []));

        /** @var FlyweightFactory $themeFactory */
        $themeFactory = $this->_objectManager->get(FlyweightFactory::class);
        /** @var CustomCss $cssService */
        $cssService = $this->_objectManager->get(CustomCss::class);
        /** @var SingleFile $singleFile */
        $singleFile = $this->_objectManager->create(
            SingleFile::class,
            ['fileService' => $cssService]
        );
        try {
            if ($this->getRequest()->getPostValue()) {
                /** @var ModelTheme $theme */
                if (!empty($themeData['theme_id'])) {
                    $theme = $themeFactory->create($themeData['theme_id']);
                } else {
                    $parentTheme = $themeFactory->create($themeData['parent_id']);
                    $theme = $parentTheme->getDomainModel(
                        ThemeInterface::TYPE_PHYSICAL
                    )->createVirtualTheme(
                        $parentTheme
                    );
                }
                if ($theme && !$theme->isEditable()) {
                    throw new LocalizedException(__('This theme is not editable.'));
                }
                $theme->addData(
                    $this->extractMutableData($themeData)
                );
                if (isset($themeData['preview']['delete'])) {
                    $theme->getThemeImage()->removePreviewImage();
                }
                $theme->getThemeImage()->uploadPreviewImage('preview');
                $theme->setType(ThemeInterface::TYPE_VIRTUAL);
                $theme->save();
                $customization = $theme->getCustomization();
                $customization->reorder(
                    Js::TYPE,
                    $reorderJsFiles
                );
                $customization->delete($removeJsFiles);
                $singleFile->update($theme, $customCssData);
                $this->messageManager->addSuccess(__('You saved the theme.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setThemeData($themeData);
            $this->_getSession()->setThemeCustomCssData($customCssData);
            $redirectBack = true;
        } catch (Exception $e) {
            $this->messageManager->addError('The theme was not saved');
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
        $redirectBack
            //phpstan:ignore
            ? $this->_redirect('adminhtml/*/edit', ['id' => $theme->getId()]) //phpcs:ignore
            : $this->_redirect('adminhtml/*/'); //phpcs:ignore
    }

    /**
     * Extract required attributes
     *
     * @param array $postData
     * @return array
     */
    private function extractMutableData(array $postData): array
    {
        if (!empty($postData['theme_title'])) {
            return ['theme_title' => $postData['theme_title']];
        }
        return [];
    }
}
