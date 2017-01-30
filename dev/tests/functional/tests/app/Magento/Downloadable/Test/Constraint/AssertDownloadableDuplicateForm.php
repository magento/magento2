<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductDuplicateForm;

/**
 * Class AssertDownloadableDuplicateForm
 */
class AssertDownloadableDuplicateForm extends AssertProductDuplicateForm
{
    /**
     * {@inheritdoc}
     */
    protected function prepareFixtureData(array $data, array $sortFields = [])
    {
        return $this->prepareDownloadableArray(parent::prepareFixtureData($data));
    }

    /**
     * Sort downloadable array
     *
     * @param array $fields
     * @return array
     */
    protected function sortDownloadableArray(array $fields)
    {
        usort(
            $fields,
            function ($row1, $row2) {
                if ($row1['sort_order'] == $row2['sort_order']) {
                    return 0;
                }

                return ($row1['sort_order'] < $row2['sort_order']) ? -1 : 1;
            }
        );

        return $fields;
    }

    /**
     * Convert fixture array
     *
     * @param array $fields
     * @return array
     */
    protected function prepareDownloadableArray(array $fields)
    {
        if (isset($fields['downloadable_links']['downloadable']['link'])) {
            $fields['downloadable_links']['downloadable']['link'] = $this->sortDownloadableArray(
                $fields['downloadable_links']['downloadable']['link']
            );
        }
        if (isset($fields['downloadable_sample']['downloadable']['sample'])) {
            $fields['downloadable_sample']['downloadable']['sample'] = $this->sortDownloadableArray(
                $fields['downloadable_sample']['downloadable']['sample']
            );
        }

        return $fields;
    }
}
