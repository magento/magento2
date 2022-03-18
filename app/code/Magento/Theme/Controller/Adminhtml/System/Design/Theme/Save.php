<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class Save use to save Theme data
 * @deprecated 100.2.0
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
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

        /** @var $themeFactory \Magento\Framework\View\Design\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->get(\Magento\Framework\View\Design\Theme\FlyweightFactory::class);
        /** @var $cssService \Magento\Theme\Model\Theme\Customization\File\CustomCss */
        $cssService = $this->_objectManager->get(\Magento\Theme\Model\Theme\Customization\File\CustomCss::class);
        /** @var $singleFile \Magento\Theme\Model\Theme\SingleFile */
        $singleFile = $this->_objectManager->create(
            \Magento\Theme\Model\Theme\SingleFile::class,
            ['fileService' => $cssService]
        );
        try {
            if ($this->getRequest()->getPostValue()) {
                /** @var $theme \Magento\Theme\Model\Theme */
                if (!empty($themeData['theme_id'])) {
                    $theme = $themeFactory->create($themeData['theme_id']);
                } else {
                    $parentTheme = $themeFactory->create($themeData['parent_id']);
                    $theme = $parentTheme->getDomainModel(
                        \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
                    )->createVirtualTheme(
                        $parentTheme
                    );
                }
                if ($theme && !$theme->isEditable()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('This theme is not editable.'));
                }
                $theme->addData(
                    $this->extractMutableData($themeData)
                );
                if (isset($themeData['preview']['delete'])) {
                    $theme->getThemeImage()->removePreviewImage();
                }
                $theme->getThemeImage()->uploadPreviewImage('preview');
                $theme->setType(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL);
                $theme->save();
                $customization = $theme->getCustomization();
                $customization->reorder(
                    \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE,
                    $reorderJsFiles
                );
                $customization->delete($removeJsFiles);
                $singleFile->update($theme, $customCssData);
                $this->messageManager->addSuccess(__('You saved the theme.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setThemeData($themeData);
            $this->_getSession()->setThemeCustomCssData($customCssData);
            $redirectBack = true;
        } catch (\Exception $e) {
            $this->messageManager->addError('The theme was not saved');
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        $redirectBack
            ? $this->_redirect('adminhtml/*/edit', ['id' => $theme->getId()])
            : $this->_redirect('adminhtml/*/');
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
