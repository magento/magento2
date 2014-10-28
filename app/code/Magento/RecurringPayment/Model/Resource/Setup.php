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


/**
 * Catalog entity setup
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\RecurringPayment\Model\Resource;

class Setup extends \Magento\Catalog\Model\Resource\Setup
{
    /**
     * Default entites and attributes
     *
     * @param array|null $entities
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function installEntities($entities = null)
    {
        $attributes = array(
            'is_recurring' => array(
                'type' => 'int',
                'label' => 'Enable Recurring Payment',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required' => false,
                'note' => 'Products with recurring payment participate in catalog as nominal items.',
                'sort_order' => 1,
                'apply_to' => 'simple,virtual',
                'is_configurable' => false,
                'group' => 'Recurring Payment'
            ),
            'recurring_payment' => array(
                'type' => 'text',
                'label' => 'Recurring Payment',
                'input' => 'text',
                'backend' => 'Magento\RecurringPayment\Model\Product\Attribute\Backend\Recurring',
                'required' => false,
                'sort_order' => 2,
                'apply_to' => 'simple,virtual',
                'is_configurable' => false,
                'group' => 'Recurring Payment'
            )
        );
        foreach ($attributes as $attrCode => $attr) {
            $this->addAttribute('catalog_product', $attrCode, $attr);
        }
    }
}
