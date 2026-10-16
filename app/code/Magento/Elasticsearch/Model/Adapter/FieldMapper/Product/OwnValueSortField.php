<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

/**
 * Sort-only field of a sortable source attribute, holding the labels of the product's own value.
 *
 * The regular label field of a composite product also holds its children's labels, so sorting on it lets the
 * children decide the position. The leading underscore keeps the name clear of any attribute code.
 */
class OwnValueSortField
{
    public const PREFIX = '_sort_';
}
