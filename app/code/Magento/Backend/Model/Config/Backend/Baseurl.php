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
namespace Magento\Backend\Model\Config\Backend;

class Baseurl extends \Magento\Core\Model\Config\Value
{
    /**
     * @var \Magento\View\Asset\MergeService
     */
    protected $_mergeService;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\View\Asset\MergeService $mergeService
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\ConfigInterface $config,
        \Magento\View\Asset\MergeService $mergeService,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_mergeService = $mergeService;
        parent::__construct($context, $registry, $storeManager, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Validate a base URL field value
     *
     * @return void
     * @throws \Magento\Model\Exception
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        try {
            if (!$this->_validateUnsecure($value) && !$this->_validateSecure($value)) {
                $this->_validateFullyQualifiedUrl($value);
            }
        } catch (\Magento\Model\Exception $e) {
            $field = $this->getFieldConfig();
            $label = $field && is_array($field) ? $field['label'] : 'value';
            $msg = __('Invalid %1. %2', $label, $e->getMessage());
            $error = new \Magento\Model\Exception($msg, 0, $e);
            throw $error;
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
        $placeholders = array('{{unsecure_base_url}}');
        switch ($this->getPath()) {
            case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_URL:
                $this->_assertValuesOrUrl(array('{{base_url}}'), $value);
                break;
            case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL:
                $this->_assertStartsWithValuesOrUrl($placeholders, $value);
                break;
            case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_STATIC_URL:
            case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_CACHE_URL:
            case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_LIB_URL:
            case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL:
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
        $placeholders = array('{{unsecure_base_url}}', '{{secure_base_url}}');
        switch ($this->getPath()) {
            case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_URL:
                $this->_assertValuesOrUrl(array('{{base_url}}', '{{unsecure_base_url}}'), $value);
                break;
            case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_LINK_URL:
                $this->_assertStartsWithValuesOrUrl($placeholders, $value);
                break;
            case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL:
            case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_CACHE_URL:
            case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_LIB_URL:
            case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL:
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
     * @throws \Magento\Model\Exception
     */
    private function _assertValuesOrUrl(array $values, $value)
    {
        if (!in_array($value, $values) && !$this->_isFullyQualifiedUrl($value)) {
            throw new \Magento\Model\Exception(
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
     * @throws \Magento\Model\Exception
     */
    private function _assertStartsWithValuesOrUrl(array $values, $value)
    {
        $quoted = array_map('preg_quote', $values, array_fill(0, count($values), '/'));
        if (!preg_match('/^(' . implode('|', $quoted) . ')(.+\/)?$/', $value) && !$this->_isFullyQualifiedUrl($value)
        ) {
            throw new \Magento\Model\Exception(
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
     * @throws \Magento\Model\Exception
     */
    private function _assertStartsWithValuesOrUrlOrEmpty(array $values, $value)
    {
        if (empty($value)) {
            return;
        }
        try {
            $this->_assertStartsWithValuesOrUrl($values, $value);
        } catch (\Magento\Model\Exception $e) {
            $msg = __('%1 An empty value is allowed as well.', $e->getMessage());
            $error = new \Magento\Model\Exception($msg, 0, $e);
            throw $error;
        }
    }

    /**
     * Default validation of a URL
     *
     * @param string $value
     * @return void
     * @throws \Magento\Model\Exception
     */
    private function _validateFullyQualifiedUrl($value)
    {
        if (!$this->_isFullyQualifiedUrl($value)) {
            throw new \Magento\Model\Exception(__('Specify a fully qualified URL.'));
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
        $url = parse_url($value);
        return isset($url['scheme']) && isset($url['host']) && preg_match('/\/$/', $value);
    }

    /**
     * Clean compiled JS/CSS when updating url configuration settings
     *
     * @return void
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            switch ($this->getPath()) {
                case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_URL:
                case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL:
                case \Magento\Core\Model\Store::XML_PATH_UNSECURE_BASE_LIB_URL:
                case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_URL:
                case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL:
                case \Magento\Core\Model\Store::XML_PATH_SECURE_BASE_LIB_URL:
                    $this->_mergeService->cleanMergedJsCss();
                    break;
            }
        }
    }
}
