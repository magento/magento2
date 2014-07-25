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
namespace Magento\ConfigurableProduct\Service\V1\Product\Link;

use \Magento\Catalog\Model\ProductRepository;
use \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable;
use Magento\Framework\Exception\StateException;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Webapi\Exception;

class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var Configurable
     */
    protected $configurableType;

    /**
     * @param ProductRepository $productRepository
     * @param Configurable $configurableType
     * @internal param ConfigurableFactory $typeConfigurableFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        Configurable $configurableType
    ) {
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($productSku, $childSku)
    {
        $product = $this->productRepository->get($productSku);
        $child = $this->productRepository->get($childSku);

        $childrenIds = array_values($this->configurableType->getChildrenIds($product->getId())[0]);
        if (in_array($child->getId(), $childrenIds)) {
            throw new StateException('Product has been already attached');
        }

        $childrenIds[] = $child->getId();
        $product->setAssociatedProductIds($childrenIds);
        $product->save();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild($productSku, $childSku)
    {
        $product = $this->productRepository->get($productSku);

        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            throw new Exception(
                sprintf('Product with specified sku: %s is not a configurable product', $productSku),
                Exception::HTTP_FORBIDDEN
            );
        }

        $options = $product->getTypeInstance()->getUsedProducts($product);
        $ids = array();
        foreach ($options as $option) {
            if ($option->getSku() == $childSku) {
                continue;
            }
            $ids[] = $option->getId();
        }
        if (count($options) == count($ids)) {
            throw new NoSuchEntityException('Requested option doesn\'t exist');
        }
        $product->addData(['associated_product_ids' => $ids]);
        $product->save();

        return true;
    }
}
