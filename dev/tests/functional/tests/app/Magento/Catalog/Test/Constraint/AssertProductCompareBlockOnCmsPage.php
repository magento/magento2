<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Class AssertProductCompareBlockOnCmsPage
 */
class AssertProductCompareBlockOnCmsPage extends AbstractConstraint
{
    /**
     * Assert that Compare Products block is presented on CMS pages.
     * Block contains information about compared products
     *
     * @param array $products
     * @param CmsIndex $cmsIndex
     * @param FixtureFactory $fixtureFactory
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        array $products,
        CmsIndex $cmsIndex,
        FixtureFactory $fixtureFactory,
        BrowserInterface $browser
    ) {
        $newCmsPage = $fixtureFactory->createByCode('cmsPage', ['dataset' => '3_column_template']);
        $newCmsPage->persist();
        $browser->open($_ENV['app_frontend_url'] . $newCmsPage->getIdentifier());
        foreach ($products as &$product) {
            $product = $product->getName();
        }
        \PHPUnit\Framework\Assert::assertEquals(
            $products,
            $cmsIndex->getCompareProductsBlock()->getProducts(),
            'Compare product block contains NOT valid information about compared products.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Compare product block contains valid information about compared products.';
    }
}
