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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Form Input/Output Strip HTML tags Filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Filter;

class Date implements \Magento\Framework\Data\Form\Filter\FilterInterface
{
    /**
     * Date format
     *
     * @var string
     */
    protected $_dateFormat;

    /**
     * Local
     *
     * @var \Zend_Locale
     */
    protected $_locale;

    /**
     * Initialize filter
     *
     * @param string $format    \Magento\Framework\Stdlib\DateTime\Date input/output format
     * @param \Zend_Locale $locale
     */
    public function __construct($format = null, $locale = null)
    {
        if (is_null($format)) {
            $format = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
        }
        $this->_dateFormat = $format;
        $this->_locale = $locale;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function inputFilter($value)
    {
        $filterInput = new \Zend_Filter_LocalizedToNormalized(
            array('date_format' => $this->_dateFormat, 'locale' => $this->_locale)
        );
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(
            array('date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->_locale)
        );

        $value = $filterInput->filter($value);
        $value = $filterInternal->filter($value);
        return $value;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function outputFilter($value)
    {
        $filterInput = new \Zend_Filter_LocalizedToNormalized(
            array('date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->_locale)
        );
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(
            array('date_format' => $this->_dateFormat, 'locale' => $this->_locale)
        );

        $value = $filterInput->filter($value);
        $value = $filterInternal->filter($value);
        return $value;
    }
}
