<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Url as UrlValidator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Asset\MergeService;

/**
 * @api
 * @since 100.0.2
 */
class Baseurl extends Value
{
    /**
     * @var MergeService
     */
    protected $_mergeService;

    /**
     * @var UrlValidator
     */
    private $urlValidator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param MergeService $mergeService
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param UrlValidator|null $urlValidator
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        MergeService $mergeService,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ?UrlValidator $urlValidator = null
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->_mergeService = $mergeService;
        $this->urlValidator = $urlValidator ?? ObjectManager::getInstance()->get(UrlValidator::class);
    }

    /**
     * Validate a base URL field value
     *
     * @return void
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        try {
            if (!$this->_validateUnsecure($value) && !$this->_validateSecure($value)) {
                $this->_validateFullyQualifiedUrl($value);
            }
        } catch (LocalizedException $e) {
            $field = $this->getFieldConfig();
            $label = $field && is_array($field) ? $field['label'] : 'value';
            $msg = __('Invalid %1. %2', $label, $e->getMessage());
            throw new LocalizedException($msg, $e);
        }
    }

    /**
     * Validation sub-routine for unsecure base URLs
     *
     * @param string $value
     * @return bool
     */
    private function _validateUnsecure($value)
    {
        $placeholders = ['{{unsecure_base_url}}'];
        switch ($this->getPath()) {
            case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL:
                $this->_assertValuesOrUrl(['{{base_url}}'], $value);
                break;
            case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL:
                $this->_assertStartsWithValuesOrUrl($placeholders, $value);
                break;
            case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_STATIC_URL:
            case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL:
                $this->_assertStartsWithValuesOrUrlOrEmpty($placeholders, $value);
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Validation sub-routine for secure base URLs
     *
     * @param string $value
     * @return bool
     */
    private function _validateSecure($value)
    {
        $placeholders = ['{{unsecure_base_url}}', '{{secure_base_url}}'];
        switch ($this->getPath()) {
            case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL:
                $this->_assertValuesOrUrl(['{{base_url}}', '{{unsecure_base_url}}'], $value);
                break;
            case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL:
                $this->_assertStartsWithValuesOrUrl($placeholders, $value);
                break;
            case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL:
            case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL:
                $this->_assertStartsWithValuesOrUrlOrEmpty($placeholders, $value);
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Value equals to one of provided items or is a URL
     *
     * @param array $values
     * @param string $value
     * @return void
     * @throws LocalizedException
     */
    private function _assertValuesOrUrl(array $values, $value)
    {
        if (!in_array($value, $values) && !$this->_isFullyQualifiedUrl($value)) {
            throw new LocalizedException(
                __('Value must be a URL or one of placeholders: %1', implode(',', $values))
            );
        }
    }

    /**
     * Value starts with one of provided items or is a URL
     *
     * @param array $values
     * @param string $value
     * @return void
     * @throws LocalizedException
     */
    private function _assertStartsWithValuesOrUrl(array $values, $value)
    {
        $quoted = array_map('preg_quote', $values, array_fill(0, count($values), '/'));
        if (!preg_match('/^(' . implode('|', $quoted) . ')(.+\/)?$/', $value) && !$this->_isFullyQualifiedUrl($value)
        ) {
            throw new LocalizedException(
                __(
                    'Specify a URL or path that starts with placeholder(s): %1, and ends with "/".',
                    implode(', ', $values)
                )
            );
        }
    }

    /**
     * Value starts with, empty or is a URL
     *
     * @param array $values
     * @param string $value
     * @return void
     * @throws LocalizedException
     */
    private function _assertStartsWithValuesOrUrlOrEmpty(array $values, $value)
    {
        if (empty($value)) {
            return;
        }
        try {
            $this->_assertStartsWithValuesOrUrl($values, $value);
        } catch (LocalizedException $e) {
            $msg = __('%1 An empty value is allowed as well.', $e->getMessage());
            throw new LocalizedException($msg, $e);
        }
    }

    /**
     * Default validation of a URL
     *
     * @param string $value
     * @return void
     * @throws LocalizedException
     */
    private function _validateFullyQualifiedUrl($value)
    {
        if (!$this->_isFullyQualifiedUrl($value)) {
            throw new LocalizedException(__('Specify a fully qualified URL.'));
        }
    }

    /**
     * Whether the provided value can be considered as a fully qualified URL
     *
     * @param string $value
     * @return bool
     */
    private function _isFullyQualifiedUrl($value)
    {
        return preg_match('/\/$/', $value) && $this->urlValidator->isValid($value, ['http', 'https']);
    }

    /**
     * Clean compiled JS/CSS when updating url configuration settings
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            switch ($this->getPath()) {
                case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL:
                case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL:
                case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL:
                case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL:
                    $this->_mergeService->cleanMergedJsCss();
                    break;
            }
        }
        return parent::afterSave();
    }
}
