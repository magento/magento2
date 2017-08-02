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
 * @since 2.0.0
 */
class Layout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_cmsLayouts;

    /**
     * @param array $cmsLayouts
     * @since 2.0.0
     */
    public function __construct(array $cmsLayouts = [])
    {
        $this->_cmsLayouts = $cmsLayouts;
    }

    /**
     * Return cms layout update options
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            foreach ($this->_cmsLayouts as $layoutName => $layoutConfig) {
                $this->_options[] = ['value' => $layoutName, 'label' => $layoutConfig];
            }
            array_unshift($this->_options, ['value' => '', 'label' => __('No layout updates')]);
        }
        return $this->_options;
    }
}
