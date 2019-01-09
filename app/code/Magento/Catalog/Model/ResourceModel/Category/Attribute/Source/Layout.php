<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category\Attribute\Source;

/**
 * Catalog category landing page attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Layout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     */
    protected $_cmsLayouts;

    /**
     * @param array $cmsLayouts
     */
    public function __construct(array $cmsLayouts = [])
    {
        $this->_cmsLayouts = $cmsLayouts;
    }

    /**
     * Return cms layout update options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = [];
        foreach ($this->_cmsLayouts as $layoutName => $layoutConfig) {
            $options[] = ['value' => $layoutName, 'label' => $layoutConfig];
        }
        array_unshift($options, ['value' => '', 'label' => __('No layout updates')]);
        return $options;
    }
}
