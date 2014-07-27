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
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab;

/**
 * Theme form, Css editor tab
 *
 * @method \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css setFiles(array $files)
 * @method array getFiles()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Css extends \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab
{
    /**
     * Uploader service
     *
     * @var \Magento\Theme\Model\Uploader\Service
     */
    protected $_uploaderService;

    /**
     * Theme custom css file
     *
     * @var \Magento\Core\Model\Theme\File
     */
    protected $_customCssFile;

    /**
     * @var \Magento\Framework\Encryption\UrlCoder
     */
    protected $urlCoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Theme\Model\Uploader\Service $uploaderService
     * @param \Magento\Framework\Encryption\UrlCoder $urlCoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Theme\Model\Uploader\Service $uploaderService,
        \Magento\Framework\Encryption\UrlCoder $urlCoder,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $formFactory, $objectManager, $data);
        $this->_uploaderService = $uploaderService;
        $this->urlCoder = $urlCoder;
    }

    /**
     * Create a form element with necessary controls
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $this->_addThemeCssFieldset();

        $customFiles = $this->_getCurrentTheme()->getCustomization()->getFilesByType(
            \Magento\Theme\Model\Theme\Customization\File\CustomCss::TYPE
        );
        $this->_customCssFile = reset($customFiles);
        $this->_addCustomCssFieldset();

        $formData['custom_css_content'] = $this->_customCssFile ? $this->_customCssFile->getContent() : null;

        /** @var $session \Magento\Backend\Model\Session */
        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
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
            array('legend' => __('Theme CSS'), 'class' => 'fieldset-wide')
        );
        $this->_addElementTypes($themeFieldset);

        $links = array();
        /** @var \Magento\Framework\View\Asset\LocalInterface $asset */
        foreach ($this->getFiles() as $fileId => $asset) {
            $links[$fileId] = array(
                'href'      => $this->getDownloadUrl($fileId, $this->_getCurrentTheme()->getId()),
                'label'     => $fileId,
                'title'     => $asset->getPath(),
                'delimiter' => '<br />'
            );
        }
        $themeFieldset->addField(
            'theme_css_view_assets',
            'links',
            array(
                'label'  => __('Theme CSS Assets'),
                'title'  => __('Theme CSS Assets'),
                'name'   => 'links',
                'values' => $links,
            )
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
            array('legend' => __('Custom CSS'), 'class' => 'fieldset-wide')
        );
        $this->_addElementTypes($themeFieldset);

        $themeFieldset->addField(
            'css_file_uploader',
            'css_file',
            array(
                'name' => 'css_file_uploader',
                'label' => __('Select CSS File to Upload'),
                'title' => __('Select CSS File to Upload'),
                'accept' => 'text/css',
                'note' => $this->_getUploadCssFileNote()
            )
        );

        $themeFieldset->addField(
            'css_uploader_button',
            'button',
            array('name' => 'css_uploader_button', 'value' => __('Upload CSS File'), 'disabled' => 'disabled')
        );

        $downloadButtonConfig = array(
            'name' => 'css_download_button',
            'value' => __('Download CSS File'),
            'onclick' => "setLocation('" . $this->getUrl(
                'adminhtml/*/downloadCustomCss',
                array('theme_id' => $this->_getCurrentTheme()->getId())
            ) . "');"
        );
        if (!$this->_customCssFile) {
            $downloadButtonConfig['disabled'] = 'disabled';
        }
        $themeFieldset->addField('css_download_button', 'button', $downloadButtonConfig);

        /** @var $imageButton \Magento\Backend\Block\Widget\Button */
        $imageButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            array(
                'id' => 'css_images_manager',
                'label' => __('Manage'),
                'class' => 'button',
                'onclick' => "MediabrowserUtility.openDialog('" . $this->getUrl(
                    'adminhtml/system_design_wysiwyg_files/index',
                    array(
                        'target_element_id' => 'custom_css_content',
                        \Magento\Theme\Helper\Storage::PARAM_THEME_ID => $this->_getCurrentTheme()->getId(),
                        \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE =>
                            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE
                    )
                ) . "', null, null,'" . $this->escapeQuote(
                    __('Upload Images'),
                    true
                ) . "');"
            )
        );

        $themeFieldset->addField(
            'css_browse_image_button',
            'note',
            array('label' => __("Images Assets"), 'text' => $imageButton->toHtml())
        );

        /** @var $fontButton \Magento\Backend\Block\Widget\Button */
        $fontButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            array(
                'id' => 'css_fonts_manager',
                'label' => __('Manage'),
                'class' => 'button',
                'onclick' => "MediabrowserUtility.openDialog('" . $this->getUrl(
                    'adminhtml/system_design_wysiwyg_files/index',
                    array(
                        'target_element_id' => 'custom_css_content',
                        \Magento\Theme\Helper\Storage::PARAM_THEME_ID => $this->_getCurrentTheme()->getId(),
                        \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE =>
                            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT
                    )
                ) . "', null, null,'" . $this->escapeQuote(
                    __('Upload Fonts'),
                    true
                ) . "');"
            )
        );

        $themeFieldset->addField(
            'css_browse_font_button',
            'note',
            array('label' => __("Fonts Assets"), 'text' => $fontButton->toHtml())
        );

        $themeFieldset->addField(
            'custom_css_content',
            'textarea',
            array('label' => __('Edit custom.css'), 'title' => __('Edit custom.css'), 'name' => 'custom_css_content')
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
        $messages = array(
            __('Allowed file types *.css.'),
            __('This file will replace the current custom.css file and can\'t be more than 2 MB.')
        );
        $maxFileSize = $this->_objectManager->get('Magento\Framework\File\Size')->getMaxFileSizeInMb();
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
        $linksElement = 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\Links';
        $fileElement = 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File';
        return array('links' => $linksElement, 'css_file' => $fileElement);
    }

    /**
     * Return Tab label
     *
     * @return string
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
            array('theme_id' => $themeId, 'file' => $this->urlCoder->encode($fileId))
        );
    }
}
