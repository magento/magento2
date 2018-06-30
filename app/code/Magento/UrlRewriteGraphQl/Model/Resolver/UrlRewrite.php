<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDTO;

/**
 * UrlRewrite field resolver, used for GraphQL request processing.
 */
class UrlRewrite implements ResolverInterface
{
    private $urlFinder;
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        ValueFactory $valueFactory,
        UrlFinderInterface $urlFinder
    ) {
        $this->valueFactory = $valueFactory;
        $this->urlFinder = $urlFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        if (!isset($value['model'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        /** @var AbstractModel $entity */
        $entity = $value['model'];
        $entityId = $entity->getEntityId();
        
        $urlRewritesCollection = $this->urlFinder->findAllByData([UrlRewriteDTO::ENTITY_ID => $entityId]);
        $urlRewrites = [];

        /** @var UrlRewriteDTO $urlRewrite */
        foreach ($urlRewritesCollection as $urlRewrite) {
            if ($urlRewrite->getRedirectType() !== 0) {
                continue;
            }

            $urlRewrites[] = [
                'url' => $urlRewrite->getRequestPath(),
                'parameters' => $this->getUrlParameters($urlRewrite->getTargetPath())
            ];
        }

        $result = function () use ($urlRewrites) {
            return $urlRewrites;
        };

        return $this->valueFactory->create($result);
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
