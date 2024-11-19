<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Swatches\Model\Swatch;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

class Form extends FormData
{
    /**
     * Form constructor.
     *
     * @param Factory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param FormKey $formKey
     * @param array $data
     * @param Json|null $serializer Serializer that allow convert arrays to string.
     */
    public function __construct(
        Factory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        FormKey $formKey,
        array $data = [],
        private ?Json $serializer = null
    ) {
        parent::__construct($factoryElement, $factoryCollection, $formKey, $data);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Add additional data when type is swatch.
     *
     * @param array $values
     * @return $this
     */
    public function addValues($values)
    {
        if (!is_array($values)) {
            return $this;
        }
        $values = array_merge(
            $values,
            $this->getAdditionalData($values)
        );
        if (isset($values['frontend_input']) && 'select' == $values['frontend_input']
            && isset($values[Swatch::SWATCH_INPUT_TYPE_KEY])
        ) {
            $values['frontend_input'] = 'swatch_' . $values[Swatch::SWATCH_INPUT_TYPE_KEY];
        }

        return parent::addValues($values);
    }

    /**
     * Check if key additional_data is set and unserialize the data.
     *
     * @param array $values
     * @return array
     */
    protected function getAdditionalData(array $values)
    {
        $additionalData = [];
        if (isset($values['additional_data'])) {
            $additionalData = $this->serializer->unserialize($values['additional_data']);
            if (!is_array($additionalData)) {
                $additionalData = [];
            }
        }

        return $additionalData;
    }
}
