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

/**
 * Backend grid item renderer datetime
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Date format string
     *
     * @var string
     */
    protected static $_format = null;

    /**
     * Retrieve datetime format
     *
     * @return string|null
     */
    protected function _getFormat()
    {
        $format = $this->getColumn()->getFormat();
        if (!$format) {
            if (is_null(self::$_format)) {
                try {
                    self::$_format = $this->_localeDate->getDateTimeFormat(
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
        if ($data = $this->_getValue($row)) {
            $format = $this->_getFormat();
            try {
                $data = $this->_localeDate->date(
                    $data,
                    \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
                )->toString(
                    $format
                );
            } catch (\Exception $e) {
                $data = $this->_localeDate->date(
                    $data,
                    \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
                )->toString(
                    $format
                );
            }
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}
