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
 * Magento data selector form element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime;

class Date extends AbstractElement
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Date
     */
    protected $_value;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = array()
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('text');
        $this->setExtType('textfield');
        if (isset($data['value'])) {
            $this->setValue($data['value']);
        }
    }

    /**
     * If script executes on x64 system, converts large
     * numeric values to timestamp limit
     *
     * @param int $value
     * @return int
     */
    protected function _toTimestamp($value)
    {

        $value = (int)$value;
        if ($value > 3155760000) {
            $value = 0;
        }

        return $value;
    }

    /**
     * Set date value
     * If \Magento\Framework\Stdlib\DateTime\Date instance is provided instead of value, other params will be ignored.
     * Format and locale must be compatible with \Magento\Framework\Stdlib\DateTime\Date
     *
     * @param mixed $value
     * @param string $format
     * @param string $locale
     * @return $this
     */
    public function setValue($value, $format = null, $locale = null)
    {
        if (empty($value)) {
            $this->_value = '';
            return $this;
        }
        if ($value instanceof \Magento\Framework\Stdlib\DateTime\DateInterface) {
            $this->_value = $value;
            return $this;
        }
        if (preg_match('/^[0-9]+$/', $value)) {
            $this->_value = new \Magento\Framework\Stdlib\DateTime\Date($this->_toTimestamp($value));
            //$this->_value = new \Magento\Framework\Stdlib\DateTime\Date((int)value);
            return $this;
        }
        // last check, if input format was set
        if (null === $format) {
            $format = DateTime::DATETIME_INTERNAL_FORMAT;
            if ($this->getInputFormat()) {
                $format = $this->getInputFormat();
            }
        }
        // last check, if locale was set
        if (null === $locale) {
            if (!($locale = $this->getLocale())) {
                $locale = null;
            }
        }
        try {
            $this->_value = new \Magento\Framework\Stdlib\DateTime\Date($value, $format, $locale);
        } catch (\Exception $e) {
            $this->_value = '';
        }
        return $this;
    }

    /**
     * Get date value as string.
     * Format can be specified, or it will be taken from $this->getFormat()
     *
     * @param string $format (compatible with \Magento\Framework\Stdlib\DateTime\Date
     * @return string
     */
    public function getValue($format = null)
    {
        if (empty($this->_value)) {
            return '';
        }
        if (null === $format) {
            $format = $this->getDateFormat();
            $format .= ($format && $this->getTimeFormat()) ? ' ' : '';
            $format .= $this->getTimeFormat() ? $this->getTimeFormat() : '';
        }
        return $this->_value->toString($format);
    }

    /**
     * Get value instance, if any
     *
     * @return \Magento\Framework\Stdlib\DateTime\Date
     */
    public function getValueInstance()
    {
        if (empty($this->_value)) {
            return null;
        }
        return $this->_value;
    }

    /**
     * Output the input field and assign calendar instance to it.
     * In order to output the date:
     * - the value must be instantiated (\Magento\Framework\Stdlib\DateTime\Date)
     * - output format must be set (compatible with \Magento\Framework\Stdlib\DateTime\Date)
     *
     * @throws \Exception
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('input-text');
        $dateFormat = $this->getDateFormat() ?: $this->getFormat();
        $timeFormat = $this->getTimeFormat();
        if (empty($dateFormat)) {
            throw new \Exception(
                'Output format is not specified. ' .
                'Please, specify "format" key in constructor, or set it using setFormat().'
            );
        }

        $dataInit = 'data-mage-init="' . $this->_escape(
            json_encode(
                array(
                    'calendar' => array(
                        'dateFormat' => $dateFormat,
                        'showsTime' => !empty($timeFormat),
                        'timeFormat' => $timeFormat,
                        'buttonImage' => $this->getImage(),
                        'buttonText' => 'Select Date',
                        'disabled' => $this->getDisabled()
                    )
                )
            )
        ) . '"';

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s %s />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes()),
            $dataInit
        );
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
