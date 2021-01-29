<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

use Magento\TestFramework\Store\ExecuteInStoreContext;

/**
 * Class checks checkbox bundle options appearance on second store view.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class OptionViewOnStoreTest extends AbstractBundleOptionsViewTest
{
    /**
     * @var ExecuteInStoreContext
     */
    private $executeInStoreContext;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/fixed_bundle_product_on_second_website.php
     *
     * @return void
     */
    public function testOptionsViewOnSecondStore(): void
    {
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'processMultiSelectionsView'],
            'fixed_bundle_product_with_special_price',
            'Option 1 on second store',
            ['Simple Product', 'Simple Product2', 'Simple Product 3'],
            true
        );
        $this->processMultiSelectionsView(
            'fixed_bundle_product_with_special_price',
            'Option 1',
            ['Simple Product', 'Simple Product2', 'Simple Product 3'],
            true
        );
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredSelectXpath(): string
    {
        return "//input[@type='radio' and contains(@data-validate, 'validate-one-required-by-name')"
            . "and contains(@class, 'bundle option')]/../label//span[normalize-space(text()) = '%s']";
    }

    /**
     * @inheritdoc
     */
    protected function getNotRequiredSelectXpath(): string
    {
        return '';
    }
}
