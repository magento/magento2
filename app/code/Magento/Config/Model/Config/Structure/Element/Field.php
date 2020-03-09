<?php
/**
 * Represents a Field Element on the UI that can be configured via xml.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element;

/**
 * Element field.
 *
 * @api
 * @since 100.0.2
 */
class Field extends \Magento\Config\Model\Config\Structure\AbstractElement
{
    /**
     * Default value for useEmptyValueOption for service option
     */
    const DEFAULT_INCLUDE_EMPTY_VALUE_OPTION = false;

    /**
     * Backend model factory
     *
     * @var \Magento\Config\Model\Config\BackendFactory
     */
    protected $_backendFactory;

    /**
     * Source model factory
     *
     * @var \Magento\Config\Model\Config\SourceFactory
     */
    protected $_sourceFactory;

    /**
     * Comment model factory
     *
     * @var \Magento\Config\Model\Config\CommentFactory
     */
    protected $_commentFactory;

    /**
     *
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\Mapper
     */
    protected $_dependencyMapper;

    /**
     * Block factory
     *
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Config\Model\Config\BackendFactory $backendFactory
     * @param \Magento\Config\Model\Config\SourceFactory $sourceFactory
     * @param \Magento\Config\Model\Config\CommentFactory $commentFactory
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param Dependency\Mapper $dependencyMapper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Config\Model\Config\BackendFactory $backendFactory,
        \Magento\Config\Model\Config\SourceFactory $sourceFactory,
        \Magento\Config\Model\Config\CommentFactory $commentFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Config\Model\Config\Structure\Element\Dependency\Mapper $dependencyMapper
    ) {
        parent::__construct($storeManager, $moduleManager);
        $this->_backendFactory = $backendFactory;
        $this->_sourceFactory = $sourceFactory;
        $this->_commentFactory = $commentFactory;
        $this->_blockFactory = $blockFactory;
        $this->_dependencyMapper = $dependencyMapper;
    }

    /**
     * Retrieve field label
     *
     * @param string $labelPrefix
     * @return string
     */
    public function getLabel($labelPrefix = '')
    {
        $label = '';
        if ($labelPrefix) {
            $label .= $this->_translateLabel($labelPrefix) . ' ';
        }
        $label .= parent::getLabel();
        return $label;
    }

    /**
     * Retrieve field hint
     *
     * @return string
     */
    public function getHint()
    {
        return $this->_getTranslatedAttribute('hint');
    }

    /**
     * Retrieve comment
     *
     * @param string $currentValue
     * @return string
     */
    public function getComment($currentValue = '')
    {
        $comment = '';
        if (isset($this->_data['comment'])) {
            if (is_array($this->_data['comment'])) {
                if (isset($this->_data['comment']['model'])) {
                    $model = $this->_commentFactory->create($this->_data['comment']['model']);
                    $comment = $model->getCommentText($currentValue);
                }
            } else {
                $comment = parent::getComment();
            }
        }
        return $comment;
    }

    /**
     * Retrieve tooltip text
     *
     * @return string
     */
    public function getTooltip()
    {
        if (isset($this->_data['tooltip'])) {
            return $this->_getTranslatedAttribute('tooltip');
        } elseif (isset($this->_data['tooltip_block'])) {
            return $this->_blockFactory->createBlock($this->_data['tooltip_block'])->toHtml();
        }
        return '';
    }

    /**
     * Retrieve field type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_data['type'] ?? 'text';
    }

    /**
     * Get required elements paths for the field
     *
     * @param string $fieldPrefix
     * @param string $elementType
     * @return string[]
     */
    protected function _getRequiredElements($fieldPrefix = '', $elementType = 'group')
    {
        $elements = [];
        if (isset($this->_data['requires'][$elementType])) {
            if (isset($this->_data['requires'][$elementType]['id'])) {
                $elements[] = $this->_getPath($this->_data['requires'][$elementType]['id'], $fieldPrefix);
            } else {
                foreach ($this->_data['requires'][$elementType] as $element) {
                    $elements[] = $this->_getPath($element['id'], $fieldPrefix);
                }
            }
        }
        return $elements;
    }

    /**
     * Get required groups paths for the field
     *
     * @param string $fieldPrefix
     * @return string[]
     */
    public function getRequiredGroups($fieldPrefix = '')
    {
        return $this->_getRequiredElements($fieldPrefix, 'group');
    }

    /**
     * Get required fields paths for the field
     *
     * @param string $fieldPrefix
     * @return string[]
     */
    public function getRequiredFields($fieldPrefix = '')
    {
        return $this->_getRequiredElements($fieldPrefix, 'field');
    }

    /**
     * Retrieve frontend css class
     *
     * @return string
     */
    public function getFrontendClass()
    {
        return $this->_data['frontend_class'] ?? '';
    }

    /**
     * Check whether field has backend model
     *
     * @return bool
     */
    public function hasBackendModel()
    {
        return array_key_exists('backend_model', $this->_data) && $this->_data['backend_model'];
    }

    /**
     * Retrieve backend model
     *
     * @return \Magento\Framework\App\Config\ValueInterface
     */
    public function getBackendModel()
    {
        return $this->_backendFactory->create($this->_data['backend_model']);
    }

    /**
     * Retrieve field section id
     *
     * @return string
     */
    public function getSectionId()
    {
        $parts = explode('/', $this->getConfigPath() ?: $this->getPath());
        return current($parts);
    }

    /**
     * Retrieve field group path
     *
     * @return string
     */
    public function getGroupPath()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return dirname($this->getConfigPath() ?: $this->getPath());
    }

    /**
     * Retrieve config path
     *
     * @return null|string
     */
    public function getConfigPath()
    {
        return $this->_data['config_path'] ?? null;
    }

    /**
     * Check whether field should be shown in default scope
     *
     * @return bool
     */
    public function showInDefault()
    {
        return isset($this->_data['showInDefault']) && (int)$this->_data['showInDefault'];
    }

    /**
     * Check whether field should be shown in website scope
     *
     * @return bool
     */
    public function showInWebsite()
    {
        return isset($this->_data['showInWebsite']) && (int)$this->_data['showInWebsite'];
    }

    /**
     * Check whether field should be shown in store scope
     *
     * @return bool
     */
    public function showInStore()
    {
        return isset($this->_data['showInStore']) && (int)$this->_data['showInStore'];
    }

    /**
     * Check if the field can be restored to default
     *
     * @return bool
     * @since 100.1.0
     */
    public function canRestore()
    {
        return isset($this->_data['canRestore']) && (int)$this->_data['canRestore'];
    }

    /**
     * Populate form element with field data
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $formField
     * @return void
     */
    public function populateInput($formField)
    {
        $originalData = [];
        foreach ($this->_data as $key => $value) {
            if (!is_array($value)) {
                $originalData[$key] = $value;
            }
        }
        $formField->setOriginalData($originalData);
    }

    /**
     * Check whether field has validation class
     *
     * @return bool
     */
    public function hasValidation()
    {
        return isset($this->_data['validate']);
    }

    /**
     * Retrieve field validation class
     *
     * @return string
     */
    public function getValidation()
    {
        return $this->_data['validate'] ?? null;
    }

    /**
     * Check whether field can be empty
     *
     * @return bool
     */
    public function canBeEmpty()
    {
        return isset($this->_data['can_be_empty']);
    }

    /**
     * Check whether field has source model
     *
     * @return bool
     */
    public function hasSourceModel()
    {
        return isset($this->_data['source_model']);
    }

    /**
     * Check whether field has options or source model
     *
     * @return bool
     */
    public function hasOptions()
    {
        return isset($this->_data['source_model']) || isset($this->_data['options']);
    }

    /**
     * Retrieve static options or source model option list
     *
     * @return array
     */
    public function getOptions()
    {
        if (isset($this->_data['source_model'])) {
            $sourceModel = $this->_data['source_model'];
            $optionArray = $this->_getOptionsFromSourceModel($sourceModel);
            return $optionArray;
        } elseif (isset($this->_data['options']) && isset($this->_data['options']['option'])) {
            $options = $this->_data['options']['option'];
            $options = $this->_getStaticOptions($options);
            return $options;
        }
        return [];
    }

    /**
     * Get Static Options list
     *
     * @param array $options
     * @return array
     */
    protected function _getStaticOptions(array $options)
    {
        foreach (array_keys($options) as $key) {
            $options[$key]['label'] = $this->_translateLabel($options[$key]['label']);
            $options[$key]['value'] = $this->_fillInConstantPlaceholders($options[$key]['value']);
        }
        return $options;
    }

    /**
     * Translate a label
     *
     * @param string $label an option label that should be translated
     * @return \Magento\Framework\Phrase
     */
    private function _translateLabel($label)
    {
        return __($label);
    }

    /**
     * Takes a string and searches for placeholders ({{CONSTANT_NAME}}) to replace with a constant value.
     *
     * @param string $value an option value that may contain a placeholder for a constant value
     * @return mixed|string the value after being replaced by the constant if needed
     */
    private function _fillInConstantPlaceholders($value)
    {
        if (is_string($value) && preg_match('/^{{(\\\\[A-Z][\\\\A-Za-z\d_]+::[A-Z\d_]+)}}$/', $value, $matches)) {
            $value = constant($matches[1]);
        }
        return $value;
    }

    /**
     * Retrieve options list from source model
     *
     * @param string $sourceModel Source model class name or class::method
     * @return array
     */
    protected function _getOptionsFromSourceModel($sourceModel)
    {
        $method = false;
        if (preg_match('/^([^:]+?)::([^:]+?)$/', $sourceModel, $matches)) {
            array_shift($matches);
            list($sourceModel, $method) = array_values($matches);
        }

        $sourceModel = $this->_sourceFactory->create($sourceModel);
        if ($sourceModel instanceof \Magento\Framework\DataObject) {
            $sourceModel->setPath($this->getPath());
        }
        if ($method) {
            if ($this->getType() == 'multiselect') {
                $optionArray = $sourceModel->{$method}();
            } else {
                $optionArray = [];
                foreach ($sourceModel->{$method}() as $key => $value) {
                    if (is_array($value)) {
                        $optionArray[] = $value;
                    } else {
                        $optionArray[] = ['label' => $value, 'value' => $key];
                    }
                }
            }
        } else {
            $optionArray = $sourceModel->toOptionArray($this->getType() == 'multiselect');
        }
        return $optionArray;
    }

    /**
     * Retrieve field dependencies
     *
     * @param string $fieldPrefix
     * @param string $storeCode
     * @return array
     */
    public function getDependencies($fieldPrefix, $storeCode)
    {
        $dependencies = [];
        if (false == isset($this->_data['depends']['fields'])) {
            return $dependencies;
        }
        $dependencies = $this->_dependencyMapper->getDependencies(
            $this->_data['depends']['fields'],
            $storeCode,
            $fieldPrefix
        );
        return $dependencies;
    }

    /**
     * Check whether element should be displayed for advanced users
     *
     * @return bool
     */
    public function isAdvanced()
    {
        return isset($this->_data['advanced']) && $this->_data['advanced'];
    }
}
