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
namespace Magento\Bundle\Service\V1\Data\Product;

use Magento\Bundle\Model\Option as OptionModel;
use Magento\Bundle\Model\OptionFactory;
use Magento\Catalog\Model\Product;

/**
 * @codeCoverageIgnore
 */
class OptionConverter
{
    /**
     * @var OptionBuilder
     */
    private $builder;
    /**
     * @var \Magento\Bundle\Model\OptionFactory
     */
    private $optionFactory;

    /**
     * @param OptionBuilder $builder
     * @param OptionFactory $optionFactory
     */
    public function __construct(
        OptionBuilder $builder,
        OptionFactory $optionFactory
    ) {
        $this->builder = $builder;
        $this->optionFactory = $optionFactory;
    }

    /**
     * @param OptionModel $option
     * @param Product $product
     * @param Link[] $productLinks
     * @return Option
     */
    public function createDataFromModel(OptionModel $option, Product $product, $productLinks = null)
    {
        $this->builder->populateWithArray($option->getData())
            ->setId($option->getId())
            ->setTitle(is_null($option->getTitle()) ? $option->getDefaultTitle() : $option->getTitle())
            ->setSku($product->getSku())
            ->setProductLinks($productLinks);
        return $this->builder->create();
    }

    /**
     * @param Option $option
     * @param Product $product
     * @return OptionModel
     */
    public function createModelFromData(Option $option, Product $product)
    {
        $optionModel = $this->optionFactory->create();
        $optionModel->addData($option->__toArray())
            ->unsetData($optionModel->getIdFieldName())
            ->setParentId($product->getId())
            ->setDefaultTitle($option->getTitle());
        return $optionModel;
    }

    /**
     * @param Option $option
     * @param OptionModel $optionModel
     * @return OptionModel
     */
    public function getModelFromData(Option $option, OptionModel $optionModel)
    {
        $newOptionModel = $this->optionFactory->create();
        $newOptionModel->setData($optionModel->getData())
            ->addData($option->__toArray())
            ->setId($optionModel->getId())
            ->setDefaultTitle(is_null($option->getTitle()) ? $optionModel->getTitle() : $option->getTitle());
        return $newOptionModel;
    }
}
