<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver;

use Magento\CmsGraphQl\Model\Resolver\Cms\CmsBlockDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * CMS blocks field resolver, used for GraphQL request processing.
 */
class CmsBlocks implements ResolverInterface
{
    /**
     * @var CmsBlockDataProvider
     */
    private $cmsBlockDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param CmsBlockDataProvider $cmsBlockDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        CmsBlockDataProvider $cmsBlockDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->valueFactory = $valueFactory;
        $this->cmsBlockDataProvider = $cmsBlockDataProvider;
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
    ) : Value {

        $cmsBlockListData = [];
        $cmsBlockIdentifiers = $this->getCmsBlockIdentifiers($args);

        foreach ($cmsBlockIdentifiers as $cmsBlockIdentifier) {
            try {
                $cmsBlockListData[$cmsBlockIdentifier] = $this->cmsBlockDataProvider->getCmsBlockById(
                    $cmsBlockIdentifier
                );
            } catch (NoSuchEntityException $ex) {
                $cmsBlockListData[$cmsBlockIdentifier] = new GraphQlInputException(
                    __(
                        'CMS block with "%1" ID does not found',
                        $cmsBlockIdentifier
                    )
                );
            }
        }

        $cmsBlocksData = [
            'items' => $cmsBlockListData
        ];

        $result = function () use ($cmsBlocksData) {
            return !empty($cmsBlocksData) ? $cmsBlocksData : [];
        };

        return $this->valueFactory->create($result);
    }

    /**
     * Retrieve CMS block identifiers to retrieve
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getCmsBlockIdentifiers($args)
    {
        if (!isset($args['identifiers']) && is_array($args['identifiers']) && count($args['identifiers']) > 0) {
            throw new GraphQlInputException(__('"identifiers" of CMS blocks should be specified'));
        }

        return (array) $args['identifiers'];
    }
}
