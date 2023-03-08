<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\File\Size;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\Image;
use Magento\Theme\Model\Theme\Collection;
use Magento\Theme\Model\Theme\Collection as ThemeCollection;

/**
 * Theme form, general tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class General extends AbstractTab
{
    /**
     * Whether theme is editable
     *
     * @var bool
     */
    protected $_isThemeEditable = false;

    /**
     * @var Size
     */
    protected $_fileSize;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ObjectManagerInterface $objectManager
     * @param Size $fileSize
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ObjectManagerInterface $objectManager,
        Size $fileSize,
        array $data = []
    ) {
        $this->_fileSize = $fileSize;
        parent::__construct($context, $registry, $formFactory, $objectManager, $data);
    }

    /**
     * Create a form element with necessary controls
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var Session $session */
        $session = $this->_objectManager->get(Session::class);
        $formDataFromSession = $session->getThemeData();
        $this->_isThemeEditable = $this->_getCurrentTheme()->isEditable();
        /** @var ThemeInterface $currentTheme */
        $currentTheme = $this->_getCurrentTheme();
        $formData = $currentTheme->getData();
        if ($formDataFromSession && isset($formData['theme_id'])) {
            unset($formDataFromSession['preview_image']);
            $formData = array_merge($formData, $formDataFromSession);
            $session->setThemeData(null);
        }
        $this->setIsThemeExist(isset($formData['theme_id']));

        /** @var FormData $form */
        $form = $this->_formFactory->create();
        $this->_addThemeFieldset($form, $formData, $currentTheme);
        if (!$this->getIsThemeExist()) {
            $formData = array_merge($formData, $this->_getDefaults());
        }
        $form->addValues($formData);
        $form->setFieldNameSuffix('theme');
        $this->setForm($form);
        return $this;
    }

    /**
     * Add theme fieldset
     *
     * @param FormData $form
     * @param array $formData
     * @param ThemeInterface $theme
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _addThemeFieldset($form, $formData, ThemeInterface $theme)
    {
        $themeFieldset = $form->addFieldset('theme', ['legend' => __('Theme Settings')]);
        $this->_addElementTypes($themeFieldset);

        if (isset($formData['theme_id'])) {
            $themeFieldset->addField('theme_id', 'hidden', ['name' => 'theme_id']);
        }

        /** @var Collection $themesCollections */
        $themesCollections = $this->_objectManager->create(ThemeCollection::class);

        /** @var JsonHelper $helper */
        $helper = $this->_objectManager->get(JsonHelper::class);

        $themesCollections->addConstraint(Collection::CONSTRAINT_AREA, Area::AREA_FRONTEND);
        $onChangeScript = sprintf(
            'parentThemeOnChange(this.value, %s)',
            str_replace(
                '"',
                '\'',
                $helper->jsonEncode($this->_getDefaultsInherited($themesCollections))
            )
        );

        /** @var ThemeInterface $parentTheme */
        $parentTheme = $this->_objectManager->create(ThemeInterface::class);
        if (!empty($formData['parent_id'])) {
            $parentTheme->load($formData['parent_id']);
        }

        if ($this->_getCurrentTheme()->isObjectNew()) {
            $themeFieldset->addField(
                'parent_id',
                'select',
                [
                    'label'    => __('Parent Theme'),
                    'title'    => __('Parent Theme'),
                    'name'     => 'parent_id',
                    'values'   => $themesCollections->toOptionArray(!$parentTheme->getId()),
                    'required' => true,
                    'class'    => 'no-changes',
                    'onchange' => $onChangeScript
                ]
            );
        } elseif (!empty($formData['parent_id'])) {
            $themeFieldset->addField(
                'parent_title',
                'note',
                [
                    'label'    => __('Parent Theme'),
                    'title'    => __('Parent Theme'),
                    'name'     => 'parent_title',
                    'text'     => $parentTheme->getId() ? $parentTheme->getThemeTitle() : ''
                ]
            );
        }

        if (!empty($formData['theme_path'])) {
            $themeFieldset->addField(
                'theme_path',
                'label',
                ['label' => __('Theme Path'), 'title' => __('Theme Path'), 'name' => 'theme_code']
            );
        }

        $themeFieldset->addField(
            'theme_title',
            $this->_getFieldTextType(),
            [
                'label' => __('Theme Title'),
                'title' => __('Theme Title'),
                'name' => 'theme_title',
                'required' => $this->_isFieldAttrRequired()
            ]
        );

        if ($this->_isThemeEditable) {
            $themeFieldset->addField(
                'preview_image',
                'image',
                [
                    'label'    => __('Theme Preview Image'),
                    'title'    => __('Theme Preview Image'),
                    'name'     => 'preview',
                    'required' => false,
                    'note'     => $this->_getPreviewImageNote(),
                    'theme'    => $theme
                ]
            );
        } elseif ($theme->hasPreviewImage()) {
            $themeFieldset->addField(
                'preview_image',
                'note',
                [
                    'label'    => __('Theme Preview Image'),
                    'title'    => __('Theme Preview Image'),
                    'name'     => 'preview',
                    'after_element_html' => '<a href="'
                    . $theme->getThemeImage()->getPreviewImageUrl()
                    . '" onclick="imagePreview(\'theme_preview_image\'); return false;">'
                    . '<img width="50" src="'
                    . $theme->getThemeImage()->getPreviewImageUrl()
                    . '" id="theme_preview_image" /></a>'
                ]
            );
        }

        return $this;
    }

    /**
     * No field notes if theme is not editable
     *
     * @param string $text
     * @return string
     */
    protected function _filterFieldNote($text)
    {
        return $this->_isThemeEditable ? $text : '';
    }

    /**
     * Field is not marked as required if theme is not editable
     *
     * @return bool
     */
    protected function _isFieldAttrRequired()
    {
        return $this->_isThemeEditable ? true : false;
    }

    /**
     * Text field replaced to label if theme is not editable
     *
     * @return string
     */
    protected function _getFieldTextType()
    {
        return $this->_isThemeEditable ? 'text' : 'label';
    }

    /**
     * Set additional form field type for theme preview image
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $element = Image::class;
        return ['image' => $element];
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Get theme default values
     *
     * @return array
     */
    protected function _getDefaults()
    {
        $defaults = [];
        $defaults['theme_title'] = __('New Theme');

        return $defaults;
    }

    /**
     * Get theme default values while inheriting other theme
     *
     * @param array $themesCollections
     * @return array
     */
    protected function _getDefaultsInherited($themesCollections)
    {
        $data = ['' => $this->_getDefaults()];

        /** @var ThemeInterface $theme */
        foreach ($themesCollections as $theme) {
            $theme->load($theme->getThemePath(), 'theme_path');
            if (!$theme->getId()) {
                continue;
            }
            $data[$theme->getId()] = ['theme_title' => __('Copy of %1', $theme->getThemeTitle())];
        }

        return $data;
    }

    /**
     * Get note string for theme's preview image
     *
     * @return Phrase
     */
    protected function _getPreviewImageNote()
    {
        $maxImageSize = $this->_fileSize->getMaxFileSizeInMb();
        if ($maxImageSize) {
            return __('Max image size %1M', $maxImageSize);
        } else {
            return __('Something is wrong with the file upload settings.');
        }
    }
}
