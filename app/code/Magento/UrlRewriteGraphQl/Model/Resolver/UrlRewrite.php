<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDTO;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Returns URL rewrites list for the specified product
 */
class UrlRewrite implements ResolverInterface
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var array
     */
    private $entityTypeMapping;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param TypeResolver $typeResolver
     * @param MetadataPool $metadataPool
     * @param array $entityTypeMapping
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        TypeResolver $typeResolver,
        MetadataPool $metadataPool,
        array $entityTypeMapping = []
    ) {
        $this->urlFinder = $urlFinder;
        $this->typeResolver = $typeResolver;
        $this->metadataPool = $metadataPool;
        $this->entityTypeMapping = $entityTypeMapping;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var  AbstractModel $entity */
        $entity = $value['model'];
        $entityId = $entity->getEntityId();

        $resolveEntityType = $this->typeResolver->resolve($entity);
        $metadata = $this->metadataPool->getMetadata($resolveEntityType);
        $entityType = $this->getEntityType($metadata->getEavEntityType());

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $data = [
            UrlRewriteDTO::ENTITY_TYPE => $entityType,
            UrlRewriteDTO::ENTITY_ID => $entityId,
            UrlRewriteDTO::STORE_ID => $storeId
        ];

        $urlRewriteCollection = $this->urlFinder->findAllByData($data);

        $urlRewrites = [];

        /** @var UrlRewriteDTO $urlRewrite */
        foreach ($urlRewriteCollection as $urlRewrite) {
            if ($urlRewrite->getRedirectType() !== 0) {
                continue;
            }

            $urlRewrites[] = [
                'url' => $urlRewrite->getRequestPath(),
                'parameters' => $this->getUrlParameters($urlRewrite->getTargetPath())
            ];
        }

        return $urlRewrites;
    }

    /**
     * Parses target path and extracts parameters
     *
     * @param string $targetPath
     * @return array
     */
    private function getUrlParameters(string $targetPath): array
    {
        $urlParameters = [];
        $targetPathParts = explode('/', trim($targetPath, '/'));
        $count = count($targetPathParts) - 1;

        /** $index starts from 3 to eliminate catalog/product/view/ part and fetch only name,
         value data from from target path */
        //phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($index = 3; $index < $count; $index += 2) {
            $urlParameters[] = [
                'name' => $targetPathParts[$index],
                'value' => $targetPathParts[$index + 1]
            ];
        }
        return $urlParameters;
    }

    /**
     * Get the entity type
     *
     * @param string $entityTypeMetadata
     * @return string
     */
    private function getEntityType(string $entityTypeMetadata) : string
    {
        $entityType = '';
        if ($entityTypeMetadata) {
            $entityType = $this->entityTypeMapping[$entityTypeMetadata];
        }
        return $entityType;
    }
}
