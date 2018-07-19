<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver;

use Magento\CmsGraphQl\Model\Resolver\DataProvider\Block as BlockDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * CMS blocks field resolver, used for GraphQL request processing
 */
class Blocks implements ResolverInterface
{
    /**
     * @var BlockDataProvider
     */
    private $blockDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param BlockDataProvider $blockDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        BlockDataProvider $blockDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->blockDataProvider = $blockDataProvider;
        $this->valueFactory = $valueFactory;
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
    ) : Value {

        $result = function () use ($args) {
            $blockIdentifiers = $this->getBlockIdentifiers($args);
            $blocksData = $this->getBlocksData($blockIdentifiers);

            $resultData = [
                'items' => $blocksData,
            ];
            return $resultData;
        };
        return $this->valueFactory->create($result);
    }

    /**
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getBlockIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of CMS blocks should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * @param array $blockIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getBlocksData(array $blockIdentifiers): array
    {
        $blocksData = [];
        try {
            foreach ($blockIdentifiers as $blockIdentifier) {
                $blocksData[$blockIdentifier] = $this->blockDataProvider->getData($blockIdentifier);
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $blocksData;
    }
}
