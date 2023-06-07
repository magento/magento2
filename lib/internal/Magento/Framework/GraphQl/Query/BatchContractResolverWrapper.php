<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchServiceContractResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequest;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Default logic to make batch contract resolvers work.
 */
class BatchContractResolverWrapper implements ResolverInterface, ResetAfterRequestInterface
{
    /**
     * @var BatchServiceContractResolverInterface
     */
    private $resolver;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var callable|null
     */
    private $contract;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var ResolveRequest[]
     */
    private $requests = [];

    /**
     * @var array|null
     */
    private $result;

    /**
     * BatchContractResolverWrapper constructor.
     * @param BatchServiceContractResolverInterface $resolver
     * @param ObjectManagerInterface $objectManager
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        BatchServiceContractResolverInterface $resolver,
        ObjectManagerInterface $objectManager,
        ValueFactory $valueFactory
    ) {
        $this->resolver = $resolver;
        $this->objectManager = $objectManager;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Get batch service contract instance.
     *
     * @return callable
     */
    private function getContact(): callable
    {
        if (!$this->contract) {
            $contractData = $this->resolver->getServiceContract();
            $this->contract = [$this->objectManager->get($contractData[0]), $contractData[1]];
            $this->objectManager = null;
        }

        return $this->contract;
    }

    /**
     * Clear aggregated data.
     *
     * @return void
     */
    private function clearAggregated(): void
    {
        $this->result = null;
        $this->arguments = [];
        $this->requests = [];
    }

    /**
     * Get resolved branch/leaf for given request.
     *
     * @param int $i
     * @throws \Throwable
     * @return mixed
     */
    private function getResolvedFor(int $i)
    {
        try {
            return $this->resolveForIndex($i);
        } catch (\Throwable  $exception) {
            $this->clearAggregated();
            throw $exception;
        }
    }

    /**
     * Resolve for specified request.
     *
     * @param int $i
     * @return mixed|\Magento\Framework\GraphQl\Query\Resolver\Value
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException
     */
    private function resolveForIndex(int $i)
    {
        if (!array_key_exists($i, $this->requests)) {
            throw new \RuntimeException('No such resolve request.');
        }

        if ($this->result === null) {
            $this->result = call_user_func($this->getContact(), $this->arguments);
        }

        if (!array_key_exists($i, $this->result)) {
            throw new \RuntimeException('Service contract returned insufficient result');
        }

        return $this->resolver->convertFromServiceResult($this->result[$i], $this->requests[$i]);
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if ($this->result !== null) {
            $this->clearAggregated();
        }

        //Add argument.
        $i = count($this->arguments);
        $request = new ResolveRequest($field, $context, $info, $value, $args);
        $this->arguments[$i] = $this->resolver->convertToServiceArgument($request);
        $this->requests[$i] = $request;

        return $this->valueFactory->create(
            function () use ($i) {
                return $this->getResolvedFor($i);
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
