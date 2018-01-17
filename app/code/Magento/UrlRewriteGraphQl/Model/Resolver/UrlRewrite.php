<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\GraphQl\Model\ResolverContextInterface;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\GraphQl\Model\ContextInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;

/**
 * UrlRewrite field resolver, used for GraphQL request processing.
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
     * {@inheritdoc}
     */
    public function resolve(array $args, ResolverContextInterface $context)
    {
        if (isset($args['url'])) {
            $urlRewrite = $this->urlFinder->findOneByData(['request_path' => $args['url']->getValue()]);
            if ($urlRewrite) {
                return [
                    'id' => $urlRewrite->getEntityId(),
                    'canonical_url' => $urlRewrite->getTargetPath(),
                    'type' => $urlRewrite->getEntityType()
                ];
            }
        }
        return null;
    }
}
