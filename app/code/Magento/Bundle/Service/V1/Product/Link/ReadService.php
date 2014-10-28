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
namespace Magento\Bundle\Service\V1\Product\Link;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Service\V1\Data\Product\LinkConverter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Webapi\Exception;

class ReadService implements ReadServiceInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\LinkConverter
     */
    private $linkConverter;

    /**
     * @param ProductRepository $productRepository
     * @param LinkConverter $linkConverter
     */
    public function __construct(
        ProductRepository $productRepository,
        LinkConverter $linkConverter
    ) {

        $this->productRepository = $productRepository;
        $this->linkConverter = $linkConverter;
    }

    /**
     * @inheritdoc
     */
    public function getChildren($productId)
    {
        $product = $this->productRepository->get($productId);

        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new Exception('Only implemented for bundle product', Exception::HTTP_FORBIDDEN);
        }

        $childrenList = [];
        foreach ($this->getOptions($product) as $option) {
            /** @var \Magento\Catalog\Model\Product $selection */
            foreach ($option->getSelections() as $selection) {
                $childrenList[] = $this->linkConverter->createDataFromModel($selection, $product);
            }
        }

        return $childrenList;
    }

    /**
     * @param Product $product
     * @return Option[]
     */
    private function getOptions(Product $product)
    {
        /** @var \Magento\Bundle\Model\Product\Type $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter(
            $product->getStoreId(),
            $product
        );

        $optionCollection = $productTypeInstance->getOptionsCollection($product);

        $selectionCollection = $productTypeInstance->getSelectionsCollection(
            $productTypeInstance->getOptionsIds($product),
            $product
        );

        $options = $optionCollection->appendSelections($selectionCollection);
        return $options;
    }
}
