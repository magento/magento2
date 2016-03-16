<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MsrpSampleData\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Msrp\Model\Product\Attribute\Source\Type;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Msrp
 *
 */
class Msrp
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var array
     */
    protected $productIds;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->productCollection = $productCollectionFactory->create()->addAttributeToSelect('sku');
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function install(array $fixtures)
    {
        $this->configWriter->save('sales/msrp/enabled', 1);
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
                $productId = $this->getProductIdBySku($row['sku']);
                if (!$productId) {
                    continue;
                }
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productCollection->getItemById($productId);
                $product->setMsrpDisplayActualPriceType(Type::TYPE_ON_GESTURE);
                if (!empty($row['msrp'])) {
                    $price = $row['msrp'];
                } else {
                    $price = $product->getPrice()*1.1;
                }
                $product->setMsrp($price);
                $product->save();
            }
        }
    }

    /**
     * Retrieve product ID by sku
     *
     * @param string $sku
     * @return int|null
     */
    protected function getProductIdBySku($sku)
    {
        if (empty($this->productIds)) {
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($this->productCollection as $product) {
                $this->productIds[$product->getSku()] = $product->getId();
            }
        }
        if (isset($this->productIds[$sku])) {
            return $this->productIds[$sku];
        }
        return null;
    }
}
