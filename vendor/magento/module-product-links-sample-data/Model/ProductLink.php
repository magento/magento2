<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductLinksSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Product links setup
 */
class ProductLink
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
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks
     */
    protected $linksInitializer;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linksInitializer
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linksInitializer
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->productFactory = $productFactory;
        $this->linksInitializer = $linksInitializer;
    }

    /**
     * {@inheritdoc}
     */
    public function install(array $related, array $upsell, array $crosssell)
    {
        $linkTypes = [
            'related' => $related,
            'upsell' => $upsell,
            'crosssell' => $crosssell
        ];

        foreach ($linkTypes as $linkType => $fixtures) {
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
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $this->productFactory->create();
                    $productId = $product->getIdBySku($row['sku']);
                    if (!$productId) {
                        continue;
                    }
                    $product->setId($productId);
                    $links = [$linkType => []];
                    foreach (explode("\n", $row['linked_sku']) as $linkedProductSku) {
                        $linkedProductId = $product->getIdBySku($linkedProductSku);
                        if ($linkedProductId) {
                            $links[$linkType][$linkedProductId] = [];
                        }
                    }
                    $this->linksInitializer->initializeLinks($product, $links);
                    $product->getLinkInstance()->saveProductRelations($product);
                }
            }
        }
    }
}
