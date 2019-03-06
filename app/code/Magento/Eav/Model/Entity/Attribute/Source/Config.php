<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Source;

/**
 * Entity/Attribute/Model - attribute selection source from configuration
 *
 * This class should be abstract, but kept usual for legacy purposes.
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Config extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     */
    protected $_optionsData;

    /**
     * @param array $options
     * @codeCoverageIgnore
     */
    public function __construct(array $options)
    {
        $this->_optionsData = $options;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadOptions(): array
    {
        $options = [];

        if (empty($this->_optionsData)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('No options found.'));
        }
        foreach ($this->_optionsData as $option) {
            $options[] = ['value' => $option['value'], 'label' => __($option['label'])];
        }

        return $options;
    }
}
