<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Rule\Condition;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Ensure that SalesRules filtering on category ignore product visibility
     *
     * @magentoAppIsolation enabled
     * @param int $categoryId
     * @param int $visibility
     * @param bool $expectedResult
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     * @magentoDataFixture Magento/SalesRule/_files/rules_category.php
     * @dataProvider validateProductConditionDataProvider
     */
    public function testValidateCategorySalesRuleIgnoresVisibility($categoryId, $visibility, $expectedResult)
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->objectManager->create(\Magento\Checkout\Model\Session::class);

        // Prepare product with given visibility and category settings
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('simple');
        $product->setVisibility($visibility);
        $product->setCategoryIds([$categoryId]);
        $product->save();

        // Load the SalesRule looking for products in a category and assert that the validation is as expected
        /** @var $rule \Magento\SalesRule\Model\Rule */
        $rule = $this->objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Category');

        $this->assertEquals($expectedResult, $rule->validate($session->getQuote()));
    }

    /**
     * @return array
     */
    public function validateProductConditionDataProvider()
    {
        $validCategoryId = 66;
        $invalidCategoryId = 2;
        $visible = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH;
        $invisible = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE;
        return [
            [
                'categoryId' => $validCategoryId,
                'visibility' => $visible,
                'expectedResult' => true
            ],
            [
                'categoryId' => $validCategoryId,
                'visibility' => $invisible,
                'expectedResult' => true
            ],
            [
                'categoryId' => $invalidCategoryId,
                'visibility' => $visible,
                'expectedResult' => false
            ],
            [
                'categoryId' => $invalidCategoryId,
                'visibility' => $invisible,
                'expectedResult' => false
            ],
        ];
    }
}
