<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Controller\Advanced;

class ResultTest extends \PHPUnit\Framework\TestCase
{
    public function testResultActionFiltersSetBeforeLoadLayout()
    {
        $filters = null;
        $expectedQuery = 'filtersData';

        $view = $this->createPartialMock(\Magento\Framework\App\View::class, ['loadLayout', 'renderLayout']);
        $view->expects($this->once())->method('loadLayout')->will(
            $this->returnCallback(
                function () use (&$filters, $expectedQuery) {
                    $this->assertEquals($expectedQuery, $filters);
                }
            )
        );

        $request = $this->createPartialMock(\Magento\Framework\App\Console\Request::class, ['getQueryValue']);
        $request->expects($this->once())->method('getQueryValue')->will($this->returnValue($expectedQuery));

        $catalogSearchAdvanced = $this->createPartialMock(
            \Magento\CatalogSearch\Model\Advanced::class,
            ['addFilters', '__wakeup']
        );
        $catalogSearchAdvanced->expects($this->once())->method('addFilters')->will(
            $this->returnCallback(
                function ($added) use (&$filters) {
                    $filters = $added;
                }
            )
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectManager->getObject(
            \Magento\Framework\App\Action\Context::class,
            ['view' => $view, 'request' => $request]
        );

        /** @var \Magento\CatalogSearch\Controller\Advanced\Result $instance */
        $instance = $objectManager->getObject(
            \Magento\CatalogSearch\Controller\Advanced\Result::class,
            ['context' => $context, 'catalogSearchAdvanced' => $catalogSearchAdvanced]
        );
        $instance->execute();
    }
}
