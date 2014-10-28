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
namespace Magento\Backend\Model\Config\Source\Email;

class Identity implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Email Identity options
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Configuration structure
     *
     * @var \Magento\Backend\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     */
    public function __construct(\Magento\Backend\Model\Config\Structure $configStructure)
    {
        $this->_configStructure = $configStructure;
    }

    /**
     * Retrieve list of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            /** @var $section \Magento\Backend\Model\Config\Structure\Element\Section */
            $section = $this->_configStructure->getElement('trans_email');

            /** @var $group \Magento\Backend\Model\Config\Structure\Element\Group */
            foreach ($section->getChildren() as $group) {
                $this->_options[] = array(
                    'value' => preg_replace('#^ident_(.*)$#', '$1', $group->getId()),
                    'label' => $group->getLabel()
                );
            }
            ksort($this->_options);
        }
        return $this->_options;
    }
}
