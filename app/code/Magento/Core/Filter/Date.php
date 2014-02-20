<?php
/**
 * Date filter. Converts date from localized to internal format.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @todo move this class to library when locale interface is moved
 */
namespace Magento\Core\Filter;

use Magento\Core\Model\LocaleInterface;

class Date implements \Zend_Filter_Interface
{
    /**
     * Filter that converts localized input into normalized format
     *
     * @var \Zend_Filter_LocalizedToNormalized
     */
    protected $_localToNormalFilter;

    /**
     * Filter that converts normalized input into internal format
     *
     * @var \Zend_Filter_NormalizedToLocalized
     */
    protected $_normalToLocalFilter;

    /**
     * @param LocaleInterface $locale
     */
    public function __construct(
        LocaleInterface $locale
    ) {
        $this->_localToNormalFilter = new \Zend_Filter_LocalizedToNormalized(array(
            'date_format' => $locale->getDateFormat(LocaleInterface::FORMAT_TYPE_SHORT)
        ));
        $this->_normalToLocalFilter = new \Zend_Filter_NormalizedToLocalized(array(
            'date_format' => \Magento\Stdlib\DateTime::DATE_INTERNAL_FORMAT
        ));
    }

    /**
     * Convert date from localized to internal format
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        return $this->_normalToLocalFilter->filter($this->_localToNormalFilter->filter($value));
    }
}
