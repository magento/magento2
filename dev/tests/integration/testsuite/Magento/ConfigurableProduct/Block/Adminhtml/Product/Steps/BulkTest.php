<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps;

use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ui\Block\Component\StepsWizard;
use PHPUnit\Framework\TestCase;

/**
 * Covers rendering of the "Bulk Images, Price and Quantity" configurable product wizard step.
 *
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class BulkTest extends TestCase
{
    /**
     * Assert that "isResizeEnabled" flag is always set as a valid JSON value
     *
     * @magentoConfigFixture default/system/upload_configuration/enable_resize 0
     */
    public function testInitScriptIsValidJsonWhenResizeIsDisabled(): void
    {
        $config = $this->renderBlockHtml();

        $this->assertSame(0, $config['isResizeEnabled']);
    }

    /**
     * Assert that "isResizeEnabled" flag is always set as a valid JSON value
     *
     * @magentoConfigFixture default/system/upload_configuration/enable_resize 1
     */
    public function testInitScriptIsValidJsonWhenResizeIsEnabled(): void
    {
        $config = $this->renderBlockHtml();

        $this->assertSame(1, $config['isResizeEnabled']);
    }

    /**
     * Render the Bulk block and return the decoded "components" json
     *
     * @return array
     */
    private function renderBlockHtml(): array
    {
        /** @var Layout $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);

        $parentBlock = $layout->createBlock(StepsWizard::class, 'steps-wizard');

        $block = $layout->createBlock(
            Bulk::class,
            'bulk',
            [
                'data' => [
                    'view_model' => Bootstrap::getObjectManager()->get(
                        \Magento\ConfigurableProduct\ViewModel\UploadResizeConfigValue::class
                    ),
                ],
            ]
        );
        $block->setTemplate('Magento_ConfigurableProduct::catalog/product/edit/attribute/steps/bulk.phtml');
        $layout->setChild($parentBlock->getNameInLayout(), $block->getNameInLayout(), 'bulk');

        $html = $block->toHtml();

        $this->assertMatchesRegularExpression(
            '/<script type="text\/x-magento-init">(.*?)<\/script>/s',
            $html
        );
        preg_match('/<script type="text\/x-magento-init">(.*?)<\/script>/s', $html, $matches);

        $json = json_decode($matches[1], true);
        $components = $json['*']['Magento_Ui/js/core/app']['components']['steps-wizard_bulk'];

        return $components;
    }
}
