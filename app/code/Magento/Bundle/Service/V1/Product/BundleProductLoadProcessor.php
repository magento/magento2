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

namespace Magento\Bundle\Service\V1\Product;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Service\V1\Product\Option\ReadService as OptionReadService;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Service\V1\Product\ProductLoadProcessorInterface;

/**
 * Add bundle product attributes to products during load.
 */
class BundleProductLoadProcessor implements ProductLoadProcessorInterface
{
    /**
     * @var OptionReadService
     */
    private $optionReadService;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param OptionReadService $optionReadService
     * @param ProductRepository $productRepository
     */
    public function __construct(
        OptionReadService $optionReadService,
        ProductRepository $productRepository
    ) {
        $this->optionReadService = $optionReadService;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id, \Magento\Catalog\Service\V1\Data\ProductBuilder $productBuilder)
    {
        /** @var \Magento\Catalog\Model\Product */
        $product = $this->productRepository->get($id);
        if ($product->getTypeId() != ProductType::TYPE_BUNDLE) {
            return;
        }

        $productBuilder->setCustomAttribute(
            'bundle_product_options',
            $this->optionReadService->getList($product->getSku())
        );
    }
}
