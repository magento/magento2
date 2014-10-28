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
namespace Magento\Catalog\Service\V1\Category;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Service\V1\Data\Category;
use Magento\Catalog\Service\V1\Data\CategoryBuilder;
use Magento\Catalog\Service\V1\Data\Eav\Category\Info\ConverterFactory;
use Magento\Catalog\Service\V1\Data\Eav\Category\Info\MetadataBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

class ReadService implements ReadServiceInterface
{
    /**
     * @var CategoryBuilder
     */
    private $builder;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var ConverterFactory
     */
    private $converterFactory;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Service\V1\Data\Eav\Category\Info\MetadataBuilder $builder
     * @param \Magento\Catalog\Service\V1\Data\Eav\Category\Info\ConverterFactory $converterFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        MetadataBuilder $builder,
        ConverterFactory $converterFactory
    ) {
        $this->builder = $builder;
        $this->categoryFactory = $categoryFactory;
        $this->converterFactory = $converterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function info($categoryId)
    {
        $category = $this->getCategory($categoryId);

        $metadata = $this->converterFactory->create(['builder' => $this->builder])
            ->createDataFromModel($category);

        return $metadata;
    }

    /**
     * @param int $id
     * @return CategoryModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategory($id)
    {
        /** @var CategoryModel $category */
        $category = $this->categoryFactory->create();
        $category->load($id);

        if (!$category->getId()) {
            throw NoSuchEntityException::singleField(Category::ID, $id);
        }
        return $category;
    }
}
