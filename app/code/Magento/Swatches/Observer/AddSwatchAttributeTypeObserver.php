<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Observer;

use Magento\Config\Model\Config\Source;
use Magento\Framework\DataObject;
use Magento\Framework\Module\Manager;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Swatches\Model\Swatch;

/**
 * Observer model
 */
class AddSwatchAttributeTypeObserver implements ObserverInterface
{
    /**
     * @param Manager $moduleManager
     */
    public function __construct(
        protected readonly Manager $moduleManager
    ) {
    }

    /**
     * Execute.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_Swatches')) {
            return;
        }

        /** @var DataObject $response */
        $response = $observer->getEvent()->getResponse();
        $types = $response->getTypes();
        $types[] = [
            'value' => Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
            'label' => __('Visual Swatch'),
            'hide_fields' => [
                'is_unique',
                'is_required',
                'frontend_class',
                '_scope',
                '_default_value',
                '_front_fieldset',
            ],
        ];
        $types[] = [
            'value' => Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT,
            'label' => __('Text Swatch'),
            'hide_fields' => [
                'is_unique',
                'is_required',
                'frontend_class',
                '_scope',
                '_default_value',
                '_front_fieldset',
            ],
        ];

        $response->setTypes($types);
    }
}
