<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Entity/Attribute/Model - attribute frontend abstract
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Entity\Attribute\Frontend;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Eav\Model\Cache\Type as CacheType;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory;

/**
 * EAV entity attribute form renderer.
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractFrontend implements \Magento\Eav\Model\Entity\Attribute\Frontend\FrontendInterface
{
    /**
     * Default cache tags values
     * will be used if no values in the constructor provided
     * @var array
     */
    private static $defaultCacheTags = [CacheType::CACHE_TAG, Attribute::CACHE_TAG];

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $cacheTags;

    /**
     * Reference to the attribute instance
     *
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected $_attribute;

    /**
     * @var BooleanFactory
     */
    protected $_attrBooleanFactory;

    /**
     * @param BooleanFactory $attrBooleanFactory
     * @param CacheInterface $cache
     * @param null $storeResolver @deprecated
     * @param array $cacheTags
     * @param StoreManagerInterface $storeManager
     * @param Serializer $serializer
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        BooleanFactory $attrBooleanFactory,
        CacheInterface $cache = null,
        $storeResolver = null,
        array $cacheTags = null,
        StoreManagerInterface $storeManager = null,
        Serializer $serializer = null
    ) {
        $this->_attrBooleanFactory = $attrBooleanFactory;
        $this->cache = $cache ?: ObjectManager::getInstance()->get(CacheInterface::class);
        $this->cacheTags = $cacheTags ?: self::$defaultCacheTags;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Serializer::class);
    }

    /**
     * Set attribute instance
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get attribute instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @codeCoverageIgnore
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    /**
     * Get attribute type for user interface form
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getInputType()
    {
        return $this->getAttribute()->getFrontendInput();
    }

    /**
     * Retrieve label
     *
     * @return string
     */
    public function getLabel()
    {
        $label = $this->getAttribute()->getFrontendLabel();
        if ($label === null || $label == '') {
            $label = $this->getAttribute()->getAttributeCode();
        }

        return $label;
    }

    /**
     * Retrieve localized label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLocalizedLabel()
    {
        return __($this->getLabel());
    }

    /**
     * Retrieve attribute value
     *
     * @param \Magento\Framework\DataObject $object
     * @return mixed
     */
    public function getValue(\Magento\Framework\DataObject $object)
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        if (in_array($this->getConfigField('input'), ['select', 'boolean'])) {
            $valueOption = $this->getOption($value);
            if (!$valueOption) {
                $opt = $this->_attrBooleanFactory->create();
                $options = $opt->getAllOptions();
                if ($options) {
                    foreach ($options as $option) {
                        if ($option['value'] === $value) {
                            $valueOption = $option['label'];
                        }
                    }
                }
            }
            $value = $valueOption;
        } elseif ($this->getConfigField('input') == 'multiselect') {
            $value = $this->getOption($value);
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
        }

        return $value;
    }

    /**
     * Checks if attribute is visible on frontend
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isVisible()
    {
        return $this->getConfigField('frontend_visible');
    }

    /**
     * Retrieve frontend class
     *
     * @return string
     */
    public function getClass()
    {
        $out = [];
        $out[] = $this->getAttribute()->getFrontendClass();
        if ($this->getAttribute()->getIsRequired()) {
            $out[] = 'required-entry';
        }

        $inputRuleClass = $this->_getInputValidateClass();
        if ($inputRuleClass) {
            $out[] = $inputRuleClass;
        }

        $textLengthValidateClasses = $this->getTextLengthValidateClasses();
        if (!empty($textLengthValidateClasses)) {
            $out = array_merge($out, $textLengthValidateClasses);
        }

        $out = !empty($out) ? implode(' ', array_unique(array_filter($out))) : '';
        return $out;
    }

    /**
     * Return validate class by attribute input validation rule
     *
     * @return string|false
     */
    protected function _getInputValidateClass()
    {
        $class = false;
        $validateRules = $this->getAttribute()->getValidateRules();
        if (!empty($validateRules['input_validation'])) {
            switch ($validateRules['input_validation']) {
                case 'alphanumeric':
                    $class = 'validate-alphanum';
                    break;
                case 'alphanum-with-spaces':
                    $class = 'validate-alphanum-with-spaces';
                    break;
                case 'numeric':
                    $class = 'validate-digits';
                    break;
                case 'alpha':
                    $class = 'validate-alpha';
                    break;
                case 'email':
                    $class = 'validate-email';
                    break;
                case 'url':
                    $class = 'validate-url';
                    break;
                case 'length':
                    $class = 'validate-length';
                    break;
                default:
                    $class = false;
                    break;
            }
        }
        return $class;
    }

    /**
     * Retrieve validation classes by min_text_length and max_text_length rules
     *
     * @return array
     */
    private function getTextLengthValidateClasses()
    {
        $classes = [];

        if ($this->_getInputValidateClass()) {
            $validateRules = $this->getAttribute()->getValidateRules();
            if (!empty($validateRules['min_text_length'])) {
                $classes[] = 'minimum-length-' . $validateRules['min_text_length'];
            }
            if (!empty($validateRules['max_text_length'])) {
                $classes[] = 'maximum-length-' . $validateRules['max_text_length'];
            }
            if (!empty($classes)) {
                $classes[] = 'validate-length';
            }
        }

        return $classes;
    }

    /**
     * Retrieve config field
     *
     * @param string $fieldName
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getConfigField($fieldName)
    {
        return $this->getAttribute()->getData('frontend_' . $fieldName);
    }

    /**
     * Get select options in case it's select box and options source is defined
     *
     * @return array
     */
    public function getSelectOptions()
    {
        $cacheKey = 'attribute-navigation-option-' .
            $this->getAttribute()->getAttributeCode() . '-' .
            $this->storeManager->getStore()->getId();
        $optionString = $this->cache->load($cacheKey);
        if (false === $optionString) {
            $options = $this->getAttribute()->getSource()->getAllOptions();
            $this->cache->save(
                $this->serializer->serialize($options),
                $cacheKey,
                $this->cacheTags
            );
        } else {
            $options = $this->serializer->unserialize($optionString);
        }
        return $options;
    }

    /**
     * Retrieve option by option id
     *
     * @param int $optionId
     * @return mixed|bool
     */
    public function getOption($optionId)
    {
        $source = $this->getAttribute()->getSource();
        if ($source) {
            return $source->getOptionText($optionId);
        }
        return false;
    }

    /**
     * Retrieve Input Renderer Class
     *
     * @return string|null
     * @codeCoverageIgnore
     */
    public function getInputRendererClass()
    {
        return $this->getAttribute()->getData('frontend_input_renderer');
    }
}
