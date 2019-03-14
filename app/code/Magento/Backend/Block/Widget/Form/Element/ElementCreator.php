<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\Widget\Form\Element;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Class ElementCreator
 *
 * @deprecated 101.0.1 in favour of UI component implementation
 * @package Magento\Backend\Block\Widget\Form\Element
 */
class ElementCreator
{
    /**
     * @var array
     */
    private $modifiers;

    /**
     * ElementCreator constructor.
     *
     * @param array $modifiers
     */
    public function __construct(array $modifiers = [])
    {
        $this->modifiers = $modifiers;
    }

    /**
     * Creates element
     *
     * @param Fieldset $fieldset
     * @param Attribute $attribute
     *
     * @return AbstractElement
     */
    public function create(Fieldset $fieldset, Attribute $attribute): AbstractElement
    {
        $config = $this->getElementConfig($attribute);

        if (!empty($config['rendererClass'])) {
            $fieldType = $config['inputType'] . '_' . $attribute->getAttributeCode();
            $fieldset->addType($fieldType, $config['rendererClass']);
        }

        return $fieldset
            ->addField($config['attribute_code'], $config['inputType'], $config)
            ->setEntityAttribute($attribute);
    }

    /**
     * Returns element config
     *
     * @param Attribute $attribute
     * @return array
     */
    private function getElementConfig(Attribute $attribute): array
    {
        $defaultConfig = $this->createDefaultConfig($attribute);
        $config = $this->modifyConfig($defaultConfig);

        $config['label'] = __($config['label']);

        return $config;
    }

    /**
     * Returns default config
     *
     * @param Attribute $attribute
     * @return array
     */
    private function createDefaultConfig(Attribute $attribute): array
    {
        return [
            'inputType' => $attribute->getFrontend()->getInputType(),
            'rendererClass' => $attribute->getFrontend()->getInputRendererClass(),
            'attribute_code' => $attribute->getAttributeCode(),
            'name' => $attribute->getAttributeCode(),
            'label' => $attribute->getFrontend()->getLabel(),
            'class' => $attribute->getFrontend()->getClass(),
            'required' => $attribute->getIsRequired(),
            'note' => $attribute->getNote(),
        ];
    }

    /**
     *  Modify config
     *
     * @param array $config
     * @return array
     */
    private function modifyConfig(array $config): array
    {
        if ($this->isModified($config['attribute_code'])) {
            return $this->applyModifier($config);
        }
        return $config;
    }

    /**
     * Returns bool if attribute need to modify
     *
     * @param string $attribute_code
     * @return bool
     */
    private function isModified($attribute_code): bool
    {
        return isset($this->modifiers[$attribute_code]);
    }

    /**
     * Apply modifier to config
     *
     * @param array $config
     * @return array
     */
    private function applyModifier(array $config): array
    {
        $modifiedConfig = $this->modifiers[$config['attribute_code']];
        foreach (array_keys($config) as $key) {
            if (isset($modifiedConfig[$key])) {
                $config[$key] = $modifiedConfig[$key];
            }
        }
        return $config;
    }
}
