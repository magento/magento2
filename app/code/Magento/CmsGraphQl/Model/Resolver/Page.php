<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver;

use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as PageDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * CMS page field resolver, used for GraphQL request processing
 */
class Page implements ResolverInterface
{
    /**
     * @var PageDataProvider
     */
    private $pageDataProvider;

    /**
     *
     * @param PageDataProvider $pageDataProvider
     */
    public function __construct(
        PageDataProvider $pageDataProvider
    ) {
        $this->pageDataProvider = $pageDataProvider;
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
    ) {
        if (!isset($args['id']) && !isset($args['identifier'])) {
            throw new GraphQlInputException(__('"Page id/identifier should be specified'));
        }

        if (isset($args['id'])) {
            $pageData = $this->getPageDataById($this->getPageId($args));
        } elseif (isset($args['identifier'])) {
            $pageData = $this->getPageDataByIdentifier($this->getPageIdentifier($args));
        }

        return $pageData;
    }

    /**
     * @param array $args
     * @return int
     */
    private function getPageId(array $args): int
    {
        return isset($args['id']) ? (int)$args['id'] : 0;
    }

    /**
     * @param array $args
     * @return string
     */
    private function getPageIdentifier(array $args): string
    {
        return isset($args['identifier']) ? (string)$args['identifier'] : '';
    }

    /**
     * @param int $pageId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getPageDataById(int $pageId): array
    {
        try {
            $pageData = $this->pageDataProvider->getDataByPageId($pageId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $pageData;
    }

    /**
     * @param string $pageIdentifier
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getPageDataByIdentifier(string $pageIdentifier): array
    {
        try {
            $pageData = $this->pageDataProvider->getDataByPageIdentifier($pageIdentifier);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $pageData;
    }
}
