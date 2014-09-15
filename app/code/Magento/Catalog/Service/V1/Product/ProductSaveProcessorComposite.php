<?php
/**
 *
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

namespace Magento\Catalog\Service\V1\Product;

use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;

/**
 * Composite pattern implementation for ProductSaveProcessorInterface.
 *
 * Allows multiple savers to be registered and used for product data modification.
 */
class ProductSaveProcessorComposite implements ProductSaveProcessorInterface
{
    /**
     * @var ProductSaveProcessorInterface[]
     */
    protected $productSaveProcessors = [];

    /**
     * Register product save processors.
     *
     * @param CompositeHelper $compositeHelper
     * @param array $saveProcessors Array of the processors which should be registered in the following format:
     * <pre>
     * [
     *      ['type' => $firstProcessorObject, 'sortOrder' => 15],
     *      ['type' => $secondProcessorObject, 'sortOrder' => 10],
     *      ...
     * ]
     * </pre>
     */
    public function __construct(CompositeHelper $compositeHelper, $saveProcessors = [])
    {
        $saveProcessors = $compositeHelper->filterAndSortDeclaredComponents($saveProcessors);
        foreach ($saveProcessors as $saveProcessor) {
            $this->productSaveProcessors[] = $saveProcessor['type'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Service\V1\Data\Product $productData
    ) {
        foreach ($this->productSaveProcessors as $saveProcessor) {
            $saveProcessor->create($product, $productData);
        }
        return $productData->getSku();
    }

    /**
     * {@inheritdoc}
     */
    public function afterCreate(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Service\V1\Data\Product $productData
    ) {
        foreach ($this->productSaveProcessors as $saveProcessor) {
            $saveProcessor->afterCreate($product, $productData);
        }
        return $productData->getSku();
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, \Magento\Catalog\Service\V1\Data\Product $product)
    {
        foreach ($this->productSaveProcessors as $saveProcessor) {
            $saveProcessor->update($id, $product);
        }
        return $product->getSku();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Service\V1\Data\Product $product)
    {
        foreach ($this->productSaveProcessors as $saveProcessor) {
            $saveProcessor->delete($product);
        }
    }
}
