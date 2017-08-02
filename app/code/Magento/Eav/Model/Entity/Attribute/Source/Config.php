<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Source;

/**
 * Entity/Attribute/Model - attribute selection source from configuration
 *
 * this class should be abstract, but kept usual for legacy purposes
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Config extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_optionsData;

    /**
     * @param array $options
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(array $options)
    {
        $this->_optionsData = $options;
    }

    /**
     * Retrieve all options for the source from configuration
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];

            if (empty($this->_optionsData)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('No options found.'));
            }
            foreach ($this->_optionsData as $option) {
                $this->_options[] = ['value' => $option['value'], 'label' => __($option['label'])];
            }
        }

        return $this->_options;
    }
}
