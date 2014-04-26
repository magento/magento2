<?php
/**
 * Date/Time filter. Converts datetime from localized to internal format.
 *
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
namespace Magento\Framework\Stdlib\DateTime\Filter;

class DateTime extends Date
{
    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate)
    {
        parent::__construct($localeDate);
        $this->_localToNormalFilter = new \Zend_Filter_LocalizedToNormalized(
            array(
                'date_format' => $this->_localeDate->getDateTimeFormat(
                    \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
                )
            )
        );
        $this->_normalToLocalFilter = new \Zend_Filter_NormalizedToLocalized(
            array('date_format' => \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)
        );
    }
}
