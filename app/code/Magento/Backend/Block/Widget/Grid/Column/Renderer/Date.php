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
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer date
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var int
     */
    protected $_defaultWidth = 160;

    /**
     * Date format string
     *
     * @var string
     */
    protected static $_format = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, array $data = array())
    {
        parent::__construct($context, $data);
    }

    /**
     * Retrieve date format
     *
     * @return string
     */
    protected function _getFormat()
    {
        $format = $this->getColumn()->getFormat();
        if (!$format) {
            if (is_null(self::$_format)) {
                try {
                    self::$_format = $this->_localeDate->getDateFormat(
                        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM
                    );
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                }
            }
            $format = self::$_format;
        }
        return $format;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
    {
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            $format = $this->_getFormat();
            try {
                if ($this->getColumn()->getGmtoffset()) {
                    $data = $this->_localeDate->date(
                        $data,
                        \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
                    )->toString(
                        $format
                    );
                } else {
                    $data = $this->_localeDate->date($data, \Zend_Date::ISO_8601, null, false)->toString($format);
                }
            } catch (\Exception $e) {
                if ($this->getColumn()->getTimezone()) {
                    $data = $this->_localeDate->date(
                        $data,
                        \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
                    )->toString(
                        $format
                    );
                } else {
                    $data = $this->_localeDate->date($data, null, null, false)->toString($format);
                }
            }
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}
