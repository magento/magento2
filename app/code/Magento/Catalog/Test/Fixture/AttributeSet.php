<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

class AttributeSet extends \Magento\Eav\Test\Fixture\AttributeSet
{
    private const ENTITY_TYPE = ProductAttributeInterface::ENTITY_TYPE_CODE;

    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor,
        private readonly Config $eavConfig
    ) {
        parent::__construct($serviceFactory, $dataProcessor);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply(
            array_merge(
                [
                    'entity_type_code' => self::ENTITY_TYPE,
                    'skeleton_id' => $this->eavConfig->getEntityType(self::ENTITY_TYPE)->getDefaultAttributeSetId(),
                ],
                $data
            )
        );
    }
}
