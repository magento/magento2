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
use Magento\Framework\Model\AbstractModel;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDTO;

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
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        UrlFinderInterface $urlFinder
    ) {
        $this->urlFinder = $urlFinder;
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

        /** @var AbstractModel $entity */
        $entity = $value['model'];
        $entityId = $entity->getEntityId();

        $urlRewriteCollection = $this->urlFinder->findAllByData([UrlRewriteDTO::ENTITY_ID => $entityId]);
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

        for ($i = 3; ($i < sizeof($targetPathParts) - 1); $i += 2) {
            $urlParameters[] = [
                'name' => $targetPathParts[$i],
                'value' => $targetPathParts[$i + 1]
            ];
        }

        return $urlParameters;
    }
}
