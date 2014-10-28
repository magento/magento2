<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace Magento\Catalog\Service\V1\Product\Link;

use \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks as LinksInitializer;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Catalog\Service\V1\Product\Link\Data\ProductLink;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Catalog\Model\Resource\Product as ProductResource;
use \Magento\Catalog\Service\V1\Product\Link\Data\ProductLink\ProductEntity\ConverterPool;
use Magento\Catalog\Service\V1\Product\ProductLoader;

class WriteService implements WriteServiceInterface
{
    /**
     * @var LinksInitializer
     */
    protected $linkInitializer;

    /**
     * @var Data\ProductLink\CollectionProvider
     */
    protected $entityCollectionProvider;

    /**
     * @var ProductLoader
     */
    protected $productLoader;

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $productResource;

    /**
     * @var Data\ProductLink\DataMapperInterface
     */
    protected $dataMapper;

    /**
     * @param LinksInitializer $linkInitializer
     * @param ProductLink\CollectionProvider $entityCollectionProvider
     * @param ProductLoader $productLoader
     * @param ProductResource $productResource
     * @param ProductLink\DataMapperInterface $dataMapper
     */
    public function __construct(
        LinksInitializer $linkInitializer,
        ProductLink\CollectionProvider $entityCollectionProvider,
        ProductLoader $productLoader,
        ProductResource $productResource,
        Data\ProductLink\DataMapperInterface $dataMapper
    ) {
        $this->linkInitializer = $linkInitializer;
        $this->entityCollectionProvider = $entityCollectionProvider;
        $this->productLoader = $productLoader;
        $this->productResource = $productResource;
        $this->dataMapper = $dataMapper;
    }

    /**
     * Save product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $links
     * @throws CouldNotSaveException
     * @return void
     */
    protected function saveLinks($product, array $links)
    {
        foreach ($links as $type => $linksData) {
            $links[$type] = $this->dataMapper->map($linksData);
        }
        $this->linkInitializer->initializeLinks($product, $links);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException('Invalid data provided for linked products');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assign($productSku, array $assignedProducts, $type)
    {
        $product = $this->productLoader->load($productSku);
        $assignedSkuList = array_map(
            function ($item) {
                return $item->getSku();
            },
            $assignedProducts
        );
        $linkedProductIds = $this->productResource->getProductsIdsBySkus($assignedSkuList);

        $links = [];
        /** @var Data\ProductLink[] $assignedProducts*/
        foreach ($assignedProducts as $linkedProduct) {
            $data = $linkedProduct->__toArray();
            if (!isset($linkedProductIds[$linkedProduct->getSku()])) {
                throw new NoSuchEntityException(
                    sprintf("Product with SKU \"%s\" does not exist", $linkedProduct->getSku())
                );
            }
            $data['product_id'] = $linkedProductIds[$linkedProduct->getSku()];
            $links[$linkedProductIds[$linkedProduct->getSku()]] = $data;
        }
        $this->saveLinks($product, [$type => $links]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update($productSku, Data\ProductLink $linkedProduct, $type)
    {
        $product = $this->productLoader->load($productSku);
        $linkedProductEntity = $this->productLoader->load($linkedProduct->getSku());
        $links = $this->entityCollectionProvider->getCollection($product, $type);

        if (!isset($links[$linkedProductEntity->getId()])) {
            throw new NoSuchEntityException(
                sprintf(
                    "Product with SKU \"%s\" is not linked to product with SKU %s",
                    $linkedProduct->getSku(),
                    $productSku
                )
            );
        }

        $data = $linkedProduct->__toArray();
        $data['product_id'] = $linkedProductEntity->getId();
        $links[$linkedProductEntity->getId()] = $data;
        $this->saveLinks($product, [$type => $links]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($productSku, $linkedProductSku, $type)
    {
        $linkedProduct = $this->productLoader->load($linkedProductSku);
        $product = $this->productLoader->load($productSku);
        $links = $this->entityCollectionProvider->getCollection($product, $type);

        if (!isset($links[$linkedProduct->getId()])) {
            throw new NoSuchEntityException(
                sprintf('Product with SKU %s is not linked to product with SKU %s', $linkedProductSku, $productSku)
            );
        }

        //Remove product from the linked product list
        unset($links[$linkedProduct->getId()]);

        $this->saveLinks($product, [$type => $links]);

        return true;
    }
}
