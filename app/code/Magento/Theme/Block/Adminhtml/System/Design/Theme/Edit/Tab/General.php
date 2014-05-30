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

use \Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme form, general tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class General extends \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab
{
    /**
     * Whether theme is editable
     *
     * @var bool
     */
    protected $_isThemeEditable = false;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_fileSize;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\File\Size $fileSize
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\File\Size $fileSize,
        array $data = array()
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
        /** @var $session \Magento\Backend\Model\Session */
        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
        $formDataFromSession = $session->getThemeData();
        $this->_isThemeEditable = $this->_getCurrentTheme()->isEditable();
        /** @var $currentTheme ThemeInterface */
        $currentTheme = $this->_getCurrentTheme();
        $formData = $currentTheme->getData();
        if ($formDataFromSession && isset($formData['theme_id'])) {
            unset($formDataFromSession['preview_image']);
            $formData = array_merge($formData, $formDataFromSession);
            $session->setThemeData(null);
        }
        $this->setIsThemeExist(isset($formData['theme_id']));

        /** @var \Magento\Framework\Data\Form $form */
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
     * @param \Magento\Framework\Data\Form $form
     * @param array $formData
     * @param \Magento\Core\Model\Theme|ThemeInterface $theme
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _addThemeFieldset($form, $formData, ThemeInterface $theme)
    {
        $themeFieldset = $form->addFieldset('theme', array('legend' => __('Theme Settings')));
        $this->_addElementTypes($themeFieldset);

        if (isset($formData['theme_id'])) {
            $themeFieldset->addField('theme_id', 'hidden', array('name' => 'theme_id'));
        }

        /** @var $themesCollections \Magento\Core\Model\Theme\Collection */
        $themesCollections = $this->_objectManager->create('Magento\Core\Model\Theme\Collection');

        /** @var $helper \Magento\Core\Helper\Data */
        $helper = $this->_objectManager->get('Magento\Core\Helper\Data');

        $onChangeScript = sprintf(
            'parentThemeOnChange(this.value, %s)',
            str_replace(
                '"',
                '\'',
                $helper->jsonEncode($this->_getDefaultsInherited($themesCollections->addDefaultPattern()))
            )
        );

        /** @var $parentTheme \Magento\Framework\View\Design\ThemeInterface */
        $parentTheme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        if (!empty($formData['parent_id'])) {
            $parentTheme->load($formData['parent_id']);
        }

        if ($this->_getCurrentTheme()->isObjectNew()) {
            $themeFieldset->addField(
                'parent_id',
                'select',
                array(
                    'label'    => __('Parent Theme'),
                    'title'    => __('Parent Theme'),
                    'name'     => 'parent_id',
                    'values'   => $themesCollections->toOptionArray(!$parentTheme->getId()),
                    'required' => true,
                    'class'    => 'no-changes',
                    'onchange' => $onChangeScript
                )
            );
        } elseif (!empty($formData['parent_id'])) {
            $themeFieldset->addField(
                'parent_title',
                'note',
                array(
                    'label'    => __('Parent Theme'),
                    'title'    => __('Parent Theme'),
                    'name'     => 'parent_title',
                    'text'     => $parentTheme->getId() ? $parentTheme->getThemeTitle() : ''
                )
            );
        }

        if (!empty($formData['theme_path'])) {
            $themeFieldset->addField(
                'theme_path',
                'label',
                array('label' => __('Theme Path'), 'title' => __('Theme Path'), 'name' => 'theme_code')
            );
        }

        $themeFieldset->addField(
            'theme_version',
            $this->_getFieldTextType(),
            array(
                'label' => __('Theme Version'),
                'title' => __('Theme Version'),
                'name' => 'theme_version',
                'required' => $this->_isFieldAttrRequired(),
                'note' => $this->_filterFieldNote(__('Example: 0.0.0.1 or 123.1.0.25-alpha1'))
            )
        );

        $themeFieldset->addField(
            'theme_title',
            $this->_getFieldTextType(),
            array(
                'label' => __('Theme Title'),
                'title' => __('Theme Title'),
                'name' => 'theme_title',
                'required' => $this->_isFieldAttrRequired()
            )
        );

        if ($this->_isThemeEditable) {
            $themeFieldset->addField(
                'preview_image',
                'image',
                array(
                    'label'    => __('Theme Preview Image'),
                    'title'    => __('Theme Preview Image'),
                    'name'     => 'preview',
                    'required' => false,
                    'note'     => $this->_getPreviewImageNote(),
                    'theme'    => $theme
                )
            );
        } elseif ($theme->hasPreviewImage()) {
            $themeFieldset->addField(
                'preview_image',
                'note',
                array(
                    'label'    => __('Theme Preview Image'),
                    'title'    => __('Theme Preview Image'),
                    'name'     => 'preview',
                    'after_element_html' => '<a href="'
                    . $theme->getThemeImage()->getPreviewImageUrl()
                    . '" onclick="imagePreview(\'theme_preview_image\'); return false;">'
                    . '<img width="50" src="'
                    . $theme->getThemeImage()->getPreviewImageUrl()
                    . '" id="theme_preview_image" /></a>'
                )
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
        $element = 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\Image';
        return array('image' => $element);
    }

    /**
     * Prepare label for tab
     *
     * @return string
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
        $defaults = array();
        $defaults['theme_version'] = '0.0.0.1';
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
        $data = array('' => $this->_getDefaults());

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($themesCollections as $theme) {
            $theme->load($theme->getThemePath(), 'theme_path');
            if (!$theme->getId()) {
                continue;
            }
            $data[$theme->getId()] = array('theme_title' => __('Copy of %1', $theme->getThemeTitle()));
        }

        return $data;
    }

    /**
     * Get note string for theme's preview image
     *
     * @return string
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
