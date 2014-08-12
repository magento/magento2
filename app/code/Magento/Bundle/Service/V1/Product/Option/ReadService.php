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
namespace Magento\Bundle\Service\V1\Product\Option;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Service\V1\Data\Product\OptionConverter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Webapi\Exception;

class ReadService implements ReadServiceInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var OptionConverter
     */
    private $optionConverter;
    /**
     * @var Type
     */
    private $type;

    /**
     * @param OptionConverter $optionConverter
     * @param ProductRepository $productRepository
     * @param Type $type
     */
    public function __construct(
        OptionConverter $optionConverter,
        ProductRepository $productRepository,
        Type $type
    ) {
        $this->optionConverter = $optionConverter;
        $this->productRepository = $productRepository;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function get($productSku, $optionId)
    {
        $product = $this->getProduct($productSku);
        $optionCollection = $this->type->getOptionsCollection($product);
        $optionCollection->setIdFilter($optionId);

        /** @var \Magento\Bundle\Model\Option $option */
        $option = $optionCollection->getFirstItem();
        if (!$option->getId()) {
            throw new NoSuchEntityException('Requested option doesn\'t exist');
        }
        return $this->optionConverter->createDataFromModel($option, $product);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productSku)
    {
        $product = $this->getProduct($productSku);
        $optionCollection = $this->type->getOptionsCollection($product);

        /** @var \Magento\Bundle\Service\V1\Data\Product\Option[] $optionDtoList */
        $optionDtoList = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionCollection as $option) {
            $optionDtoList[] = $this->optionConverter->createDataFromModel($option, $product);
        }
        return $optionDtoList;
    }

    /**
     * @param string $productSku
     * @return Product
     * @throws Exception
     */
    private function getProduct($productSku)
    {
        $product = $this->productRepository->get($productSku);

        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new Exception('Only implemented for bundle product', Exception::HTTP_FORBIDDEN);
        }

        return $product;
    }
}
