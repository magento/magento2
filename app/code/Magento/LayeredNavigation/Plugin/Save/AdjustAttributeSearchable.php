<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Plugin\Save;

use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;

class AdjustAttributeSearchable
{
    /**
     * Change attribute value if the filterable option is not enabled
     *
     * @param Presentation $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvertPresentationDataToInputType(Presentation $subject, array $result): array
    {
        if (isset($result['is_filterable_in_search']) &&
            $result['is_filterable_in_search'] == '1' &&
            $result['is_searchable'] == '0'
        ) {
            $result['is_filterable_in_search'] = '0';
        }

        return $result;
    }
}
