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

namespace Magento\ConfigurableProduct\Service\V1\Product\Option;

use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Service\V1\Data\Option;
use Magento\ConfigurableProduct\Service\V1\Data\OptionConverter;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory as ConfigurableAttributeFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Store\Model\Store;
use Magento\Framework\StoreManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Webapi\Exception;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var ConfigurableAttributeFactory
     */
    protected $configurableAttributeFactory;

    /**
     * Eav config
     *
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\OptionConverter
     */
    protected $optionConverter;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $productType;

    /**
     * @param ProductRepository $productRepository
     * @param ConfigurableAttributeFactory $configurableAttributeFactory
     * @param EavConfig $eavConfig
     * @param OptionConverter $optionConverter
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param ConfigurableType $productType
     */
    public function __construct(
        ProductRepository $productRepository,
        ConfigurableAttributeFactory $configurableAttributeFactory,
        EavConfig $eavConfig,
        OptionConverter $optionConverter,
        StoreManagerInterface $storeManager,
        ConfigurableType $productType
    ) {
        $this->productRepository = $productRepository;
        $this->configurableAttributeFactory = $configurableAttributeFactory;
        $this->eavConfig = $eavConfig;
        $this->optionConverter = $optionConverter;
        $this->storeManager = $storeManager;
        $this->productType = $productType;
    }

    /**
     * {@inheritdoc}
     */
    public function add($productSku, Option $option)
    {
        $product = $this->productRepository->get($productSku);
        $allowedTypes = [ProductType::TYPE_SIMPLE, ProductType::TYPE_VIRTUAL, ConfigurableType::TYPE_CODE];
        if (!in_array($product->getTypeId(), $allowedTypes)) {
            throw new \InvalidArgumentException('Incompatible product type');
        }

        $eavAttribute = $this->eavConfig->getAttribute(Product::ENTITY, $option->getAttributeId());

        /** @var $configurableAttribute \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute */
        $configurableAttribute = $this->configurableAttributeFactory->create();
        $configurableAttribute->loadByProductAndAttribute($product, $eavAttribute);
        if ($configurableAttribute->getId()) {
            throw new CouldNotSaveException('Product already has this option');
        }

        try {
            $product->setTypeId(ConfigurableType::TYPE_CODE);
            $product->setConfigurableAttributesData([$this->optionConverter->convertArrayFromData($option)]);
            $product->setStoreId($this->storeManager->getStore(Store::ADMIN_CODE)->getId());
            $product->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('An error occurred while saving option');
        }

        $configurableAttribute = $this->configurableAttributeFactory->create();
        $configurableAttribute->loadByProductAndAttribute($product, $eavAttribute);
        if (!$configurableAttribute->getId()) {
            throw new CouldNotSaveException('An error occurred while saving option');
        }

        return $configurableAttribute->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function update($productSku, $optionId, Option $option)
    {
        $product = $this->getProduct($productSku);

        $configurableAttribute = $this->configurableAttributeFactory->create();
        $configurableAttribute->load($optionId);
        if (!$configurableAttribute->getId() || $configurableAttribute->getProductId() != $product->getId()) {
            throw new NoSuchEntityException('Option with id "%1" not found', [$optionId]);
        }
        $configurableAttribute = $this->optionConverter->getModelFromData($option, $configurableAttribute);
        try {
            $configurableAttribute->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not update option with id "%1"', [$optionId]);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($productSku, $optionId)
    {
        $product = $this->getProduct($productSku);

        $attributeCollection = $this->productType->getConfigurableAttributeCollection($product);
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $option */
        $option = $attributeCollection->getItemById($optionId);

        if ($option === null) {
            throw new NoSuchEntityException('Requested option doesn\'t exist');
        }
        $option->delete();

        return true;
    }

    /**
     * @param string $productSku
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Webapi\Exception
     */
    private function getProduct($productSku)
    {
        $product = $this->productRepository->get($productSku);
        if (ConfigurableType::TYPE_CODE !== $product->getTypeId()) {
            throw new Exception(
                'Product with specified sku: "%1" is not a configurable product',
                Exception::HTTP_FORBIDDEN,
                Exception::HTTP_FORBIDDEN,
                [
                    $product->getSku()
                ]
            );
        }
        return $product;
    }
}
