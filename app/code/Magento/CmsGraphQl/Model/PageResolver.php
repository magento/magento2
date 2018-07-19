<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * CMS page field resolver, used for GraphQL request processing
 */
class PageResolver implements ResolverInterface
{
    /**
     * @var PageDataProvider
     */
    private $pageDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param PageDataProvider $pageDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        PageDataProvider $pageDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->pageDataProvider = $pageDataProvider;
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
            $pageId = $this->getPageId($args);
            $pageData = $this->getPageData($pageId);

            return $pageData;
        };
        return $this->valueFactory->create($result);
    }

    /**
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getPageId(array $args): int
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"Page id should be specified'));
        }

        return (int)$args['id'];
    }

    /**
     * @param int $pageId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getPageData(int $pageId): array
    {
        try {
            $pageData = $this->pageDataProvider->getData($pageId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $pageData;
    }
}
