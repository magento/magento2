<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Validate Eav Model before save.
 */
class Exceptions extends ArraySerialized
{
    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\View\DesignInterface $design,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?Json $serializer = null
    ) {
        $this->_design = $design;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
    }

    /**
     * Validate value
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * if there is no field value, search value is empty or regular expression is not valid
     */
    public function beforeSave()
    {
        $design = clone $this->_design;
        // For value validations
        $exceptions = $this->getValue();

        foreach ($exceptions as $rowKey => &$row) {
            unset($row['record_id']);
            // Validate that all values have come
            foreach (['search', 'value'] as $fieldName) {
                if (!isset($row[$fieldName])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('%1 does not contain field \'%2\'', $this->getData('field_config/fieldset'), $fieldName)
                    );
                }
            }

            // Empty string (match all) is not supported, because it means setting a default theme. Remove such entries.
            if (!isset($row['search']) || !strlen($row['search'])) {
                unset($exceptions[$rowKey]);
                continue;
            }

            // Validate the theme value
            $design->setDesignTheme($row['value'], \Magento\Framework\App\Area::AREA_FRONTEND);

            // Compose regular exception pattern
            $exceptions[$rowKey]['regexp'] = $this->_composeRegexp($row['search']);
        }
        $this->setValue($exceptions);

        return parent::beforeSave();
    }

    /**
     * Composes regexp by user entered value
     *
     * @param string $search
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException on invalid regular expression
     */
    protected function _composeRegexp($search)
    {
        // If valid regexp entered - do nothing
        /** @codingStandardsIgnoreStart */
        if (@preg_match($search, '') !== false) {
            return $search;
        }
        /** @codingStandardsIgnoreEnd */

        // Find out - whether user wanted to enter regexp or normal string.
        if ($this->_isRegexp($search)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid regular expression: "%1".', $search));
        }

        return '/' . preg_quote($search, '/') . '/i';
    }

    /**
     * Checks search string, whether it was intended to be a regexp or normal search string
     *
     * @param string $search
     * @return bool
     */
    protected function _isRegexp($search)
    {
        if ($search === null || strlen($search) < 3) {
            return false;
        }

        $possibleDelimiters = '/#~%';
        // Limit delimiters to reduce possibility, that we miss string with regexp.

        // Starts with a delimiter
        if (strpos($possibleDelimiters, (string) $search[0]) !== false) {
            return true;
        }

        // Ends with a delimiter and (possible) modifiers
        $pattern = '/[' . preg_quote($possibleDelimiters, '/') . '][imsxeADSUXJu]*$/';
        if (preg_match($pattern, $search)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function afterLoad()
    {
        parent::afterLoad();
        $values = $this->getValue();
        foreach ($values as &$value) {
            if (isset($value['record_id'])) {
                unset($value['record_id']);
            }
        }
        $this->setValue($values);
        return $this;
    }

    /**
     * Get Value from data array.
     *
     * @return array
     */
    public function getValue()
    {
        return $this->getData('value') ?: [];
    }
}
