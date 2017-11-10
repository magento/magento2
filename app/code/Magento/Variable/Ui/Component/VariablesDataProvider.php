<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Ui\DataProvider\AbstractDataProvider;

class VariablesDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public function getData()
    {
        $v = 1;
        return [
            'items' => [

            ]
        ];
    }
}
