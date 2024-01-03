<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class SetPaymentMethod implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'cart_id' => null,
        'method' => [
            'method' => 'checkmo',
            'po_number' => null,
            'additional_data' => null,
        ],
    ];
    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(
        ServiceFactory $serviceFactory
    ) {
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as SetPaymentMethod::DEFAULT_DATA.
     * <pre>
     *    $data = [
     *      'cart_id' => (int) Cart ID. Required
     *      'method' => (array) Payment method. Optional
     *    ]
     * </pre>
     * Fields structure:
     * - $data['method']: can be supplied in following formats:
     *      - array ["method" => "checkmo", "po_number" => null, "additional_data" => null]
     *      - string "checkmo"
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->prepareData($data);
        $service = $this->serviceFactory->create(PaymentMethodManagementInterface::class, 'set');
        $service->execute(
            [
                'cart_id' => $data['cart_id'],
                'method' => $data['method'],
            ]
        );

        return null;
    }

    /**
     * Prepare payment data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        if (isset($data['method']) && is_string($data['method'])) {
            $data['method'] = ['method' => $data['method']];
        }

        return array_merge(self::DEFAULT_DATA, $data);
    }
}
