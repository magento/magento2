<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WishlistSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Installation of sample data for wishlist
 */
class Wishlist
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var Helper;
     */
    protected $helper;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param Helper $wishlistHelper
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        Helper $wishlistHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->helper = $wishlistHelper;
        $this->wishlistFactory = $wishlistFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                /** @var \Magento\Customer\Model\Customer $customer */
                $customer = $this->helper->getCustomerByEmail($row['customer_email']);
                if (!$customer) {
                    continue;
                }

                /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
                $wishlist = $this->wishlistFactory->create();
                $wishlist->loadByCustomerId($customer->getId(), true);
                if (!$wishlist->getId()) {
                    continue;
                }
                $productSkuList = explode("\n", $row['product_list']);
                $this->helper->addProductsToWishlist($wishlist, $productSkuList);
            }
        }
    }
}
