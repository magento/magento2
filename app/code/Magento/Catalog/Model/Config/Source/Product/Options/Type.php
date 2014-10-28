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
namespace Magento\Catalog\Model\Config\Source\Product\Options;

/**
 * Product option types mode source
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Product Option Config
     *
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $_productOptionConfig;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     */
    public function __construct(\Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig)
    {
        $this->_productOptionConfig = $productOptionConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $groups = array(array('value' => '', 'label' => __('-- Please select --')));

        foreach ($this->_productOptionConfig->getAll() as $option) {
            $types = array();
            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }
                $types[] = array('label' => __($type['label']), 'value' => $type['name']);
            }
            if (count($types)) {
                $groups[] = array('label' => __($option['label']), 'value' => $types);
            }
        }

        return $groups;
    }
}
