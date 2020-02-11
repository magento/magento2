<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace  Mod\HelloWorldFrontendUi\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Ui component data provider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * Gets some data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }
}
