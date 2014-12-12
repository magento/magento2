<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ImportExport\Model\Source\Export;

/**
 * Source export entity model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Entity implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface
     */
    protected $_exportConfig;

    /**
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     */
    public function __construct(\Magento\ImportExport\Model\Export\ConfigInterface $exportConfig)
    {
        $this->_exportConfig = $exportConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => __('-- Please Select --'), 'value' => ''];
        foreach ($this->_exportConfig->getEntities() as $entityName => $entityConfig) {
            $options[] = ['value' => $entityName, 'label' => __($entityConfig['label'])];
        }
        return $options;
    }
}
