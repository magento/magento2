<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Controller\Advanced;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testResultActionFiltersSetBeforeLoadLayout()
    {
        $filters = null;
        $expectedQuery = 'filtersData';

        $view = $this->getMock('Magento\Framework\App\View', ['loadLayout', 'renderLayout'], [], '', false);
        $view->expects($this->once())->method('loadLayout')->will(
            $this->returnCallback(
                function () use (&$filters, $expectedQuery) {
                    $this->assertEquals($expectedQuery, $filters);
                }
            )
        );

        $request = $this->getMock('Magento\Framework\App\Console\Request', ['getQuery'], [], '', false);
        $request->expects($this->once())->method('getQuery')->will($this->returnValue($expectedQuery));

        $catalogSearchAdvanced = $this->getMock(
            'Magento\CatalogSearch\Model\Advanced',
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

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $context = $objectManager->getObject(
            'Magento\Framework\App\Action\Context',
            ['view' => $view, 'request' => $request]
        );

        /** @var \Magento\CatalogSearch\Controller\Advanced\Result $instance */
        $instance = $objectManager->getObject(
            'Magento\CatalogSearch\Controller\Advanced\Result',
            ['context' => $context, 'catalogSearchAdvanced' => $catalogSearchAdvanced]
        );
        $instance->execute();
    }
}
