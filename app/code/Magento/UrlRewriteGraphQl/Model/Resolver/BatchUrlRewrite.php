<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDTO;

/**
 * Returns URL rewrites lists for all entities of a request with one lookup per entity type
 */
class BatchUrlRewrite implements BatchResolverInterface
{
    /**
     * @param UrlFinderInterface $urlFinder
     * @param TypeResolver $typeResolver
     * @param MetadataPool $metadataPool
     * @param array $entityTypeMapping
     */
    public function __construct(
        private readonly UrlFinderInterface $urlFinder,
        private readonly TypeResolver $typeResolver,
        private readonly MetadataPool $metadataPool,
        private readonly array $entityTypeMapping = []
    ) {
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $entityTypes = [];
        $entityIds = [];
        foreach ($requests as $key => $request) {
            $value = $request->getValue();
            if (!isset($value['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }

            /** @var AbstractModel $entity */
            $entity = $value['model'];
            $entityType = $this->getEntityType($entity);
            $entityTypes[$key] = $entityType;
            $entityIds[$entityType] ??= [];
            if ($entity->getEntityId() !== null) {
                $entityIds[$entityType][] = $entity->getEntityId();
            }
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $urlRewrites = [];
        foreach ($entityIds as $entityType => $ids) {
            $urlRewrites[$entityType] = $ids
                ? $this->findUrlRewrites((string)$entityType, array_values(array_unique($ids)), $storeId)
                : [];
        }

        $response = new BatchResponse();
        foreach ($requests as $key => $request) {
            $entityId = $request->getValue()['model']->getEntityId();
            $response->addResponse(
                $request,
                $entityId === null ? [] : ($urlRewrites[$entityTypes[$key]][$entityId] ?? [])
            );
        }

        return $response;
    }

    /**
     * Find URL rewrites without redirect of the given entities, grouped by entity ID
     *
     * @param string $entityType
     * @param array $entityIds
     * @param int $storeId
     * @return array
     */
    private function findUrlRewrites(string $entityType, array $entityIds, int $storeId): array
    {
        $urlRewriteCollection = $this->urlFinder->findAllByData([
            UrlRewriteDTO::ENTITY_TYPE => $entityType,
            UrlRewriteDTO::ENTITY_ID => $entityIds,
            UrlRewriteDTO::STORE_ID => $storeId
        ]);

        $urlRewrites = [];
        /** @var UrlRewriteDTO $urlRewrite */
        foreach ($urlRewriteCollection as $urlRewrite) {
            if ($urlRewrite->getRedirectType() !== 0) {
                continue;
            }

            $urlRewrites[$urlRewrite->getEntityId()][] = [
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

        // Name/value pairs start after the "catalog/product/view" route part of the target path.
        for ($index = 3; $index < $count; $index += 2) {
            $urlParameters[] = [
                'name' => $targetPathParts[$index],
                'value' => $targetPathParts[$index + 1]
            ];
        }

        return $urlParameters;
    }

    /**
     * Get the URL rewrite entity type of the entity
     *
     * @param object $entity
     * @return string
     * @throws LocalizedException
     */
    private function getEntityType(object $entity): string
    {
        $metadata = $this->metadataPool->getMetadata($this->typeResolver->resolve($entity));
        $entityTypeMetadata = $metadata->getEavEntityType();
        if (!$entityTypeMetadata) {
            return '';
        }
        if (!isset($this->entityTypeMapping[$entityTypeMetadata])) {
            throw new LocalizedException(
                __('URL rewrite entity type is not configured for "%1"', $entityTypeMetadata)
            );
        }

        return $this->entityTypeMapping[$entityTypeMetadata];
    }
}
