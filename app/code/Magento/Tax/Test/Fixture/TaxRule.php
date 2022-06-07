<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class TaxRule implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'code' => '%uniqid%',
        'customer_tax_class_ids' => '%uniqid%',
        'position' => '0',
        'priority' => '0',
        'product_tax_class_ids' => ['%uniqid%'],
        'tax_rate_ids' => ['%uniqid%'],
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(TaxRuleRepositoryInterface::class, 'save');

        return $service->execute(['rule' => array_merge(self::DEFAULT_DATA, $data)]);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(TaxRuleRepositoryInterface::class, 'deleteById');
        $service->execute(['ruleId' => $data->getId()]);
    }
}
