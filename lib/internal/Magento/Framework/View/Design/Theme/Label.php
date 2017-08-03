<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme_Label class used for system configuration
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Class \Magento\Framework\View\Design\Theme\Label
 *
 */
class Label implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Labels collection array
     *
     * @var array
     */
    protected $_labelsCollection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\Theme\Label\ListInterface $labelList
     */
    public function __construct(\Magento\Framework\View\Design\Theme\Label\ListInterface $labelList)
    {
        $this->_labelsCollection = $labelList;
    }

    /**
     * Return labels collection array
     *
     * @param bool|string $label add empty values to result with specific label
     * @return array
     */
    public function getLabelsCollection($label = false)
    {
        $options = $this->_labelsCollection->getLabels();
        if ($label) {
            array_unshift($options, ['value' => '', 'label' => $label]);
        }
        return $options;
    }

    /**
     * Return labels collection for backend system configuration with empty value "No Theme"
     *
     * @return array
     */
    public function getLabelsCollectionForSystemConfiguration()
    {
        return $this->toOptionArray();
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        return $this->getLabelsCollection((string)new \Magento\Framework\Phrase('-- No Theme --'));
    }
}
