<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\Session;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\File\Size;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File as FormElementFile;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\Links;
use Magento\Theme\Helper\Storage;
use Magento\Theme\Model\Theme\Customization\File\CustomCss;
use Magento\Theme\Model\Theme\File;
use Magento\Theme\Model\Uploader\Service;
use Magento\Theme\Model\Wysiwyg\Storage as WysiwygStorage;

/**
 * Theme form, Css editor tab
 *
 * @api
 * @method Css setFiles(array $files)
 * @method array getFiles()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Css extends AbstractTab
{
    /**
     * Uploader service
     *
     * @var Service
     */
    protected $_uploaderService;

    /**
     * Theme custom css file
     *
     * @var File
     */
    protected $_customCssFile;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ObjectManagerInterface $objectManager
     * @param Service $uploaderService
     * @param UrlCoder $urlCoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ObjectManagerInterface $objectManager,
        Service $uploaderService,
        protected readonly UrlCoder $urlCoder,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $objectManager, $data);
        $this->_uploaderService = $uploaderService;
    }

    /**
     * Create a form element with necessary controls
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var FormData $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $this->_addThemeCssFieldset();

        $customFiles = $this->_getCurrentTheme()->getCustomization()->getFilesByType(
            CustomCss::TYPE
        );
        $this->_customCssFile = reset($customFiles);
        $this->_addCustomCssFieldset();

        $formData['custom_css_content'] = $this->_customCssFile ? $this->_customCssFile->getContent() : null;

        /** @var Session $session */
        $session = $this->_objectManager->get(Session::class);
        $cssFileContent = $session->getThemeCustomCssData();
        if ($cssFileContent) {
            $formData['custom_css_content'] = $cssFileContent;
            $session->unsThemeCustomCssData();
        }
        $form->addValues($formData);
        parent::_prepareForm();
        return $this;
    }

    /**
     * Set theme css fieldset
     *
     * @return $this
     */
    protected function _addThemeCssFieldset()
    {
        $form = $this->getForm();
        $themeFieldset = $form->addFieldset(
            'theme_css',
            ['legend' => __('Theme CSS'), 'class' => 'fieldset-wide']
        );
        $this->_addElementTypes($themeFieldset);

        $links = [];
        /** @var LocalInterface $asset */
        foreach ($this->getFiles() as $fileId => $asset) {
            $links[$fileId] = [
                'href'      => $this->getDownloadUrl($fileId, $this->_getCurrentTheme()->getId()),
                'label'     => $fileId,
                'title'     => $asset->getPath(),
                'delimiter' => '<br />',
            ];
        }
        $themeFieldset->addField(
            'theme_css_view_assets',
            'links',
            [
                'label'  => __('Theme CSS Assets'),
                'title'  => __('Theme CSS Assets'),
                'name'   => 'links',
                'values' => $links,
            ]
        );

        return $this;
    }

    /**
     * Set custom css fieldset
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _addCustomCssFieldset()
    {
        $form = $this->getForm();
        $themeFieldset = $form->addFieldset(
            'custom_css',
            ['legend' => __('Custom CSS'), 'class' => 'fieldset-wide']
        );
        $this->_addElementTypes($themeFieldset);

        $themeFieldset->addField(
            'css_file_uploader',
            'css_file',
            [
                'name' => 'css_file_uploader',
                'label' => __('Select CSS File to Upload'),
                'title' => __('Select CSS File to Upload'),
                'accept' => 'text/css',
                'note' => $this->_getUploadCssFileNote()
            ]
        );

        $themeFieldset->addField(
            'css_uploader_button',
            'button',
            ['name' => 'css_uploader_button', 'value' => __('Upload CSS File'), 'disabled' => 'disabled']
        );

        $downloadButtonConfig = [
            'name' => 'css_download_button',
            'value' => __('Download CSS File'),
            'onclick' => "setLocation('" . $this->getUrl(
                'adminhtml/*/downloadCustomCss',
                ['theme_id' => $this->_getCurrentTheme()->getId()]
            ) . "');",
        ];
        if (!$this->_customCssFile) {
            $downloadButtonConfig['disabled'] = 'disabled';
        }
        $themeFieldset->addField('css_download_button', 'button', $downloadButtonConfig);

        /** @var Button $imageButton */
        $imageButton = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'css_images_manager',
                'label' => __('Manage'),
                'class' => 'button',
                'onclick' => "MediabrowserUtility.openDialog('" . $this->getUrl(
                    'adminhtml/system_design_wysiwyg_files/index',
                    [
                        'target_element_id' => 'custom_css_content',
                        Storage::PARAM_THEME_ID => $this->_getCurrentTheme()->getId(),
                        Storage::PARAM_CONTENT_TYPE => WysiwygStorage::TYPE_IMAGE
                    ]
                ) . "', null, null,'" . $this->escapeJs(
                    __('Upload Images')
                ) . "');",
            ]
        );

        $themeFieldset->addField(
            'css_browse_image_button',
            'note',
            ['label' => __("Images Assets"), 'text' => $imageButton->toHtml()]
        );

        /** @var Button $fontButton */
        $fontButton = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'css_fonts_manager',
                'label' => __('Manage'),
                'class' => 'button',
                'onclick' => "MediabrowserUtility.openDialog('" . $this->getUrl(
                    'adminhtml/system_design_wysiwyg_files/index',
                    [
                        'target_element_id' => 'custom_css_content',
                        Storage::PARAM_THEME_ID => $this->_getCurrentTheme()->getId(),
                        Storage::PARAM_CONTENT_TYPE => WysiwygStorage::TYPE_FONT
                    ]
                ) . "', null, null,'" . $this->escapeJs(
                    __('Upload Fonts')
                ) . "');",
            ]
        );

        $themeFieldset->addField(
            'css_browse_font_button',
            'note',
            ['label' => __("Fonts Assets"), 'text' => $fontButton->toHtml()]
        );

        $themeFieldset->addField(
            'custom_css_content',
            'textarea',
            ['label' => __('Edit custom.css'), 'title' => __('Edit custom.css'), 'name' => 'custom_css_content']
        );

        return $this;
    }

    /**
     * Get note string for css file to Upload
     *
     * @return string
     */
    protected function _getUploadCssFileNote()
    {
        $messages = [
            __('Allowed file types *.css.'),
            __('This file will replace the current custom.css file and can\'t be more than 2 MB.'),
        ];
        $maxFileSize = $this->_objectManager->get(Size::class)->getMaxFileSizeInMb();
        if ($maxFileSize) {
            $messages[] = __('Max file size to upload %1M', $maxFileSize);
        } else {
            $messages[] = __('Something is wrong with the file upload settings.');
        }

        return implode('<br />', $messages);
    }

    /**
     * Set additional form field type for theme preview image
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $linksElement = Links::class;
        $fileElement = FormElementFile::class;
        return ['links' => $linksElement, 'css_file' => $fileElement];
    }

    /**
     * Return Tab label
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('CSS Editor');
    }

    /**
     * Get URL to download CSS file
     *
     * @param string $fileId
     * @param int $themeId
     * @return string
     */
    public function getDownloadUrl($fileId, $themeId)
    {
        return $this->getUrl(
            'adminhtml/*/downloadCss',
            ['theme_id' => $themeId, 'file' => $this->urlCoder->encode($fileId)]
        );
    }
}
