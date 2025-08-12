<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;
use Magento\UrlRewriteGraphQl\Model\DataProvider\EntityDataProviderComposite;

class Route extends AbstractEntityUrl implements ResolverInterface
{
    /**
     * @var EntityDataProviderComposite
     */
    private $entityDataProviderComposite;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param CustomUrlLocatorInterface $customUrlLocator
     * @param EntityDataProviderComposite $entityDataProviderComposite
     * @param Uid $idEncoder
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        CustomUrlLocatorInterface $customUrlLocator,
        EntityDataProviderComposite $entityDataProviderComposite,
        Uid $idEncoder
    ) {
        parent::__construct($urlFinder, $customUrlLocator, $idEncoder);
        $this->entityDataProviderComposite = $entityDataProviderComposite;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $result = null;
        $resultArray = parent::resolve(
            $field,
            $context,
            $info,
            $value,
            $args
        );
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        if ($resultArray) {
            $result = [];
            if (isset($resultArray['type'])) {
                $result = $this->entityDataProviderComposite->getData(
                    $resultArray['type'],
                    (int)$resultArray['id'],
                    $info,
                    $storeId
                );
            }
            $result['redirect_code'] = $resultArray['redirect_code'];
            $result['relative_url'] = $resultArray['relative_url'];
            $result['type'] = $resultArray['type'];
            return $result;
        }
        return null;
    }
}
