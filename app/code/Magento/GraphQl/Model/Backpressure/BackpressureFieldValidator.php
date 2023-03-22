<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Model\Backpressure;

use Magento\Framework\App\Backpressure\BackpressureExceededException;
use Magento\Framework\App\BackpressureEnforcerInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Argument\ValidatorInterface;

/**
 * Enforces backpressure for queries/mutations
 */
class BackpressureFieldValidator implements ValidatorInterface
{
    /**
     * @var BackpressureContextFactory
     */
    private BackpressureContextFactory $backpressureContextFactory;

    /**
     * @var BackpressureEnforcerInterface
     */
    private BackpressureEnforcerInterface $backpressureEnforcer;

    /**
     * @param BackpressureContextFactory $backpressureContextFactory
     * @param BackpressureEnforcerInterface $backpressureEnforcer
     */
    public function __construct(
        BackpressureContextFactory $backpressureContextFactory,
        BackpressureEnforcerInterface $backpressureEnforcer
    ) {
        $this->backpressureContextFactory = $backpressureContextFactory;
        $this->backpressureEnforcer = $backpressureEnforcer;
    }

    /**
     * Validate resolver args
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Field $field
     * @param array $args
     * @return void
     * @throws GraphQlTooManyRequestsException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(Field $field, $args): void
    {
        $context = $this->backpressureContextFactory->create($field);
        if (!$context) {
            return;
        }

        try {
            $this->backpressureEnforcer->enforce($context);
        } catch (BackpressureExceededException $exception) {
            throw new GraphQlTooManyRequestsException(__('Too Many Requests'));
        }
    }
}
