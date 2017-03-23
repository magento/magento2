<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Controller\Advanced;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testResultActionFiltersSetBeforeLoadLayout()
    {
        $filters = null;
        $expectedQuery = 'filtersData';

        $view = $this->getMock(\Magento\Framework\App\View::class, ['loadLayout', 'renderLayout'], [], '', false);
        $view->expects($this->once())->method('loadLayout')->will(
            $this->returnCallback(
                function () use (&$filters, $expectedQuery) {
                    $this->assertEquals($expectedQuery, $filters);
                }
            )
        );

        $request = $this->getMock(\Magento\Framework\App\Console\Request::class, ['getQueryValue'], [], '', false);
        $request->expects($this->once())->method('getQueryValue')->will($this->returnValue($expectedQuery));

        $catalogSearchAdvanced = $this->getMock(
            \Magento\CatalogSearch\Model\Advanced::class,
            ['addFilters', '__wakeup'],
            [],
            '',
            false
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
