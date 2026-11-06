<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype;

use Laminas\Validator\InArray;

/**
 * Validator for check input type value
 */
class Validator extends InArray
{
    /**
     * @var \Magento\Eav\Helper\Data
     */
    protected $_eavData = null;

    /**
     * @param \Magento\Eav\Helper\Data $eavData
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Eav\Helper\Data $eavData)
    {
        $this->_eavData = $eavData;
        //set data haystack
        $haystack = $this->_eavData->getInputTypesValidatorData();

        //reset message template and set custom
        $this->messageTemplates = null;
        $this->_initMessageTemplates();

        //parent construct with options
        parent::__construct(['haystack' => $haystack, 'strict' => true]);
    }

    /**
     * Initialize message templates with translating
     *
     * @return $this
     */
    protected function _initMessageTemplates()
    {
        if (!$this->messageTemplates) {
            $this->messageTemplates = [
                self::NOT_IN_ARRAY => __('Input type "%value%" not found in the input types list.'),
            ];
        }
        return $this;
    }

    /**
     * Add input type to haystack
     *
     * @param string $type
     * @return $this
     */
    public function addInputType($type)
    {
        if (!in_array((string)$type, $this->haystack, true)) {
            $this->haystack[] = (string)$type;
        }
        return $this;
    }
}
