<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Block\Adminhtml\Attribute;

use Magento\Framework\Registry;
use Magento\Eav\Model\Entity\Attribute\Config;

/**
 * Disable form fields
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class PropertyLocker
{
    /**
     * @var Config
     * @since 2.0.0
     */
    private $attributeConfig;

    /**
     * @var Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @param Registry $registry
     * @param Config $attributeConfig
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        Registry $registry,
        Config $attributeConfig
    ) {
        $this->registry = $registry;
        $this->attributeConfig = $attributeConfig;
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return void
     * @since 2.0.0
     */
    public function lock(\Magento\Framework\Data\Form $form)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attributeObject */
        $attributeObject = $this->registry->registry('entity_attribute');
        if ($attributeObject->getId()) {
            foreach ($this->attributeConfig->getLockedFields($attributeObject) as $field) {
                if ($element = $form->getElement($field)) {
                    $element->setDisabled(1);
                    $element->setReadonly(1);
                }
            }
        }
    }
}
