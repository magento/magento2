<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\AttributeConstantsInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Element\Wysiwyg as WysiwygElement;

/**
 * Class Wysiwyg
 */
class Wysiwyg extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $this->addMetaProperties($meta, [
            AttributeConstantsInterface::CODE_DESCRIPTION,
            AttributeConstantsInterface::CODE_SHORT_DESCRIPTION,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Add additional meta properties
     *
     * @param array $meta
     * @param array $fields
     * @return array
     */
    protected function addMetaProperties(array $meta, array $fields)
    {
        foreach ($fields as $attributeCode) {
            if ($this->getGroupCodeByField($meta, $attributeCode)) {
                $attributePath = $this->getElementArrayPath($meta, $attributeCode);
                $containerPath = $this->getElementArrayPath($meta, static::CONTAINER_PREFIX . $attributeCode);

                $meta = $this->arrayManager->merge($containerPath, $meta, [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Ui/js/form/components/group',
                            ],
                        ],
                    ],
                ]);

                $meta = $this->arrayManager->merge($attributePath, $meta, [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'wysiwyg' => true,
                                'formElement' => WysiwygElement::NAME,
                            ],
                        ],
                    ],
                ]);
            }
        }

        return $meta;
    }
}
