<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequest;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use RuntimeException;
use Throwable;

/**
 * Wrapper containing batching logic for BatchResolverInterface.
 */
class BatchResolverWrapper implements ResolverInterface, ResetAfterRequestInterface
{
    /**
     * @var BatchResolverInterface
     */
    private $resolver;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var ContextInterface|null
     */
    private $context;

    /**
     * @var Field|null
     */
    private $field;

    /**
     * @var BatchRequestItemInterface[]
     */
    private $request = [];

    /**
     * @var BatchResponse|null
     */
    private $response;

    /**
     * BatchResolverWrapper constructor.
     * @param BatchResolverInterface $resolver
     * @param ValueFactory $valueFactory
     */
    public function __construct(BatchResolverInterface $resolver, ValueFactory $valueFactory)
    {
        $this->resolver = $resolver;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Clear aggregated data.
     *
     * @return void
     */
    private function clearAggregated(): void
    {
        $this->response = null;
        $this->request = null;
        $this->context = null;
        $this->field = null;
    }

    /**
     * Find resolved data for given request.
     *
     * @param BatchRequestItemInterface $item
     * @throws Throwable
     * @return mixed
     */
    private function findResolvedFor(BatchRequestItemInterface $item)
    {
        try {
            return $this->resolveFor($item);
        } catch (Throwable $exception) {
            $this->clearAggregated();
            throw $exception;
        }
    }

    /**
     * Resolve branch/leaf for given item.
     *
     * @param BatchRequestItemInterface $item
     * @return mixed|Value
     * @throws Throwable
     */
    private function resolveFor(BatchRequestItemInterface $item)
    {
        if (!$this->request) {
            throw new RuntimeException('Unknown batch request item');
        }

        if (!$this->response) {
            $this->response = $this->resolver->resolve($this->context, $this->field, $this->request);
        }

        return $this->response->findResponseFor($item);
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if ($this->response) {
            $this->clearAggregated();
        }

        $item = new ResolveRequest($field, $context, $info, $value, $args);
        $this->request[] = $item;
        $this->context = $context;
        $this->field = $field;

        return $this->valueFactory->create(
            function () use ($item) {
                return $this->findResolvedFor($item);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->clearAggregated();
    }
}
