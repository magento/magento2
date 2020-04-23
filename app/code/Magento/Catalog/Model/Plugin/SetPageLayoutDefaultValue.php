<?php
/**
 * Plugin for \Magento\Catalog\Model\Category\DataProvider
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Plugin;

use Magento\Catalog\Model\Category\DataProvider;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Sets the default value for Category Design Layout if provided
 */
class SetPageLayoutDefaultValue
{
    private $defaultValue;

    /**
     * @param string $defaultValue
     */
    public function __construct(string $defaultValue = "")
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Sets the default value for Category Design Layout in data provider if provided
     *
     * @param DataProvider $subject
     * @param array $result
     * @return array
     *
     * @throws NoSuchEntityException
     */
    public function afterGetDefaultMetaData(DataProvider $subject, array $result): array
    {
        $currentCategory = $subject->getCurrentCategory();

        if ($currentCategory && !$currentCategory->getId() && array_key_exists('page_layout', $result)) {
            $result['page_layout']['default'] = $this->defaultValue ?: null;
        }

        return $result;
    }
}
