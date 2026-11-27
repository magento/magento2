<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider\Breadcrumbs as BreadcrumbsDataProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Retrieves breadcrumbs
 */
class Breadcrumbs implements ResolverInterface, ResetAfterRequestInterface
{
    /**
     * @var BreadcrumbsDataProvider
     */
    private $breadcrumbsDataProvider;

    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * Category paths whose breadcrumbs are requested but not loaded yet
     *
     * @var array<string, string>
     */
    private array $pendingPaths = [];

    /**
     * @param BreadcrumbsDataProvider $breadcrumbsDataProvider
     * @param ValueFactory|null $valueFactory
     */
    public function __construct(
        BreadcrumbsDataProvider $breadcrumbsDataProvider,
        ?ValueFactory $valueFactory = null
    ) {
        $this->breadcrumbsDataProvider = $breadcrumbsDataProvider;
        $this->valueFactory = $valueFactory ?: ObjectManager::getInstance()->get(ValueFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['path'])) {
            throw new LocalizedException(__('"path" value should be specified'));
        }

        $path = (string)$value['path'];
        $this->pendingPaths[$path] = $path;

        return $this->valueFactory->create(
            function () use ($path) {
                if ($this->pendingPaths) {
                    $paths = array_values($this->pendingPaths);
                    $this->pendingPaths = [];
                    $this->breadcrumbsDataProvider->prefetch($paths);
                }
                $breadcrumbsData = $this->breadcrumbsDataProvider->getData($path);
                return count($breadcrumbsData) ? $breadcrumbsData : null;
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->pendingPaths = [];
    }
}
