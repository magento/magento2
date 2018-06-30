<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\CmsGraphQl\Model\Resolver\Cms\CmsPageDataProvider;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Resolver\Customer\CustomerDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * CMS page field resolver, used for GraphQL request processing.
 */
class CmsPage implements ResolverInterface
{
    /**
     * @var CmsPageDataProvider
     */
    private $cmsPageDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param CmsPageDataProvider $cmsPageDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        CmsPageDataProvider $cmsPageDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->valueFactory = $valueFactory;
        $this->cmsPageDataProvider = $cmsPageDataProvider;
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

        $cmsPageId = $this->getCmsPageId($args);

        try {
            $cmsPageData = $this->cmsPageDataProvider->getCmsPageById($cmsPageId);

            $result = function () use ($cmsPageData) {
                return !empty($cmsPageData) ? $cmsPageData : [];
            };

            return $this->valueFactory->create($result);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__('CMS page with ID %1 does not exist.', [$cmsPageId]));
        }
    }

    /**
     * Retrieve CMS page ID
     *
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getCmsPageId($args)
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"id for category should be specified'));
        }

        return (int) $args['id'];
    }
}
