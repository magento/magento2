<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class Link implements DataFixtureInterface
{
    public const DEFAULT_DATA = [
        'id' => null,
        'sku' => null,
        'option_id' => null,
        'qty' => 1,
        'position' => 1,
        'is_default' => false,
        'price' => null,
        'price_type' => null,
        'can_change_quantity' => 0
    ];

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param ProcessorInterface $dataProcessor
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        ProcessorInterface $dataProcessor,
        DataObjectFactory  $dataObjectFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Link::DEFAULT_DATA.
     */
    public function apply(array $data = []): ?DataObject
    {
        return $this->dataObjectFactory->create(['data' => $this->prepareData($data)]);
    }

    /**
     * Prepare link data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        return $this->dataProcessor->process($this, $data);
    }
}
