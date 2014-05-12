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
namespace Magento\Core\Model\Design\Backend;

class Exceptions extends \Magento\Backend\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_design = $design;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Validate value
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     * if there is no field value, search value is empty or regular expression is not valid
     */
    protected function _beforeSave()
    {
        $design = clone $this->_design;
        // For value validations
        $exceptions = $this->getValue();
        foreach ($exceptions as $rowKey => $row) {
            if ($rowKey === '__empty') {
                continue;
            }

            // Validate that all values have come
            foreach (array('search', 'value') as $fieldName) {
                if (!isset($row[$fieldName])) {
                    throw new \Magento\Framework\Model\Exception(__("Exception does not contain field '{$fieldName}'"));
                }
            }

            // Empty string (match all) is not supported, because it means setting a default theme. Remove such entries.
            if (!strlen($row['search'])) {
                unset($exceptions[$rowKey]);
                continue;
            }

            // Validate the theme value
            $design->setDesignTheme($row['value'], \Magento\Framework\App\Area::AREA_FRONTEND);

            // Compose regular exception pattern
            $exceptions[$rowKey]['regexp'] = $this->_composeRegexp($row['search']);
        }
        $this->setValue($exceptions);

        return parent::_beforeSave();
    }

    /**
     * Composes regexp by user entered value
     *
     * @param string $search
     * @return string
     * @throws \Magento\Framework\Model\Exception on invalid regular expression
     */
    protected function _composeRegexp($search)
    {
        // If valid regexp entered - do nothing
        if (@preg_match($search, '') !== false) {
            return $search;
        }

        // Find out - whether user wanted to enter regexp or normal string.
        if ($this->_isRegexp($search)) {
            throw new \Magento\Framework\Model\Exception(__('Invalid regular expression: "%1".', $search));
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
        if (strlen($search) < 3) {
            return false;
        }

        $possibleDelimiters = '/#~%';
        // Limit delimiters to reduce possibility, that we miss string with regexp.

        // Starts with a delimiter
        if (strpos($possibleDelimiters, $search[0]) !== false) {
            return true;
        }

        // Ends with a delimiter and (possible) modifiers
        $pattern = '/[' . preg_quote($possibleDelimiters, '/') . '][imsxeADSUXJu]*$/';
        if (preg_match($pattern, $search)) {
            return true;
        }

        return false;
    }
}
