<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Email;

/**
 * @api
 * @since 2.0.0
 */
class Identity implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Email Identity options
     *
     * @var array
     * @since 2.0.0
     */
    protected $_options = null;

    /**
     * Configuration structure
     *
     * @var \Magento\Config\Model\Config\Structure
     * @since 2.0.0
     */
    protected $_configStructure;

    /**
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @since 2.0.0
     */
    public function __construct(\Magento\Config\Model\Config\Structure $configStructure)
    {
        $this->_configStructure = $configStructure;
    }

    /**
     * Retrieve list of options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            /** @var $section \Magento\Config\Model\Config\Structure\Element\Section */
            $section = $this->_configStructure->getElement('trans_email');

            /** @var $group \Magento\Config\Model\Config\Structure\Element\Group */
            foreach ($section->getChildren() as $group) {
                $this->_options[] = [
                    'value' => preg_replace('#^ident_(.*)$#', '$1', $group->getId()),
                    'label' => $group->getLabel(),
                ];
            }
            ksort($this->_options);
        }
        return $this->_options;
    }
}
