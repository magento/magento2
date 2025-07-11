<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer;

use Magento\Elasticsearch\Model\Adapter\FieldType\Date;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerInterface;

/**
 * Value transformer for date type fields.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class DateTransformer implements ValueTransformerInterface
{
    /**
     * @var Date
     */
    private $dateFieldType;

    /**
     * @param Date $dateFieldType
     */
    public function __construct(Date $dateFieldType)
    {
        $this->dateFieldType = $dateFieldType;
    }

    /**
     * @inheritdoc
     */
    public function transform(string $value): ?string
    {
        try {
            $formattedDate = $this->dateFieldType->formatDate(null, $value);
        } catch (\Exception $e) {
            $formattedDate = null;
        }

        return $formattedDate;
    }
}
