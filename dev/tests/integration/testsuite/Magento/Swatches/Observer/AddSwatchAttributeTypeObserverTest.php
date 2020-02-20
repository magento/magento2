<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Observer;

use Magento\Framework\DataObject;
use Magento\Swatches\Model\Swatch;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Event\ManagerInterface;

/**
 * Test checks that swatch types are added to the other attribute types
 */
class AddSwatchAttributeTypeObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea adminhtml
     */
    public function testAddSwatchAttributeTypes()
    {
        $objectManager = Bootstrap::getObjectManager();
        $eventManager = $objectManager->get(ManagerInterface::class);
        $response = new DataObject();
        $response->setTypes([]);

        $eventManager->dispatch(
            'adminhtml_product_attribute_types',
            ['response' => $response]
        );

        $responseTypes = $response->getTypes();

        self::assertGreaterThan(0, count($responseTypes));

        /* Iterate through values since other types (not swatches) might be added by observers */
        $responseTypeValues = [];
        foreach ($responseTypes as $responseType) {
            $responseTypeValues[] = $responseType['value'];
        }

        self::assertTrue(in_array(Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT, $responseTypeValues));
        self::assertTrue(in_array(Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT, $responseTypeValues));
    }
}
