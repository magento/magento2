<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert that entities are present on storefront.
 */
class AssertEntitiesOnStorefront extends AbstractConstraint
{
    /**
     * Assert that products are presents on storefront.
     *
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param array $entities
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        array $entities
    ) {
        foreach ($entities as $entity) {
            $browser->open($_ENV['app_frontend_url'] . $entity->getUrlKey() . '.html');
            \PHPUnit_Framework_Assert::assertEquals(
                $catalogProductView->getViewBlock()->getProductName(),
                $entity->getName(),
                "Can't find product in store front"
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Entities are presents on storefront.';
    }
}
