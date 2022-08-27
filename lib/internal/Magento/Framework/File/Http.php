<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Laminas\File\Transfer\Adapter\Http as LaminasHttp;

class Http extends LaminasHttp
{
    /**
     * Method to change file name.
     *
     * @return Http
     */
    protected function prepareFiles()
    {
        $http = parent::prepareFiles();

        foreach (array_keys($http->files) as $key) {
            $http->files[$key]['name'] = str_replace(
                basename($this->files[$key]['tmp_name']) . '_',
                '',
                $this->files[$key]['name']
            );
        }

        return $http;
    }
}
