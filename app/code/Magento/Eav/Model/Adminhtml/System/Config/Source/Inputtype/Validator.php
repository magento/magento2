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
namespace Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype;

/**
 * Validator for check input type value
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Validator extends \Zend_Validate_InArray
{
    /**
     * Eav data
     *
     * @var \Magento\Eav\Helper\Data
     */
    protected $_eavData = null;

    /**
     * @param \Magento\Eav\Helper\Data $eavData
     */
    public function __construct(\Magento\Eav\Helper\Data $eavData)
    {
        $this->_eavData = $eavData;
        //set data haystack
        $haystack = $this->_eavData->getInputTypesValidatorData();

        //reset message template and set custom
        $this->_messageTemplates = null;
        $this->_initMessageTemplates();

        //parent construct with options
        parent::__construct(array('haystack' => $haystack, 'strict' => true));
    }

    /**
     * Initialize message templates with translating
     *
     * @return $this
     */
    protected function _initMessageTemplates()
    {
        if (!$this->_messageTemplates) {
            $this->_messageTemplates = array(
                self::NOT_IN_ARRAY => __('Input type "%value%" not found in the input types list.')
            );
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
        if (!in_array((string)$type, $this->_haystack, true)) {
            $this->_haystack[] = (string)$type;
        }
        return $this;
    }
}
