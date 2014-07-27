<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Controller\Advanced;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testResultActionFiltersSetBeforeLoadLayout()
    {
        $filters = null;
        $expectedQuery = 'filtersData';

        $view = $this->getMock('Magento\Framework\App\View', array('loadLayout', 'renderLayout'), array(), '', false);
        $view->expects($this->once())->method('loadLayout')->will(
            $this->returnCallback(
                function () use (&$filters, $expectedQuery) {
                    $this->assertEquals($expectedQuery, $filters);
                }
            )
        );

        $request = $this->getMock('Magento\Framework\App\Console\Request', array('getQuery'), array(), '', false);
        $request->expects($this->once())->method('getQuery')->will($this->returnValue($expectedQuery));

        $catalogSearchAdvanced = $this->getMock(
            'Magento\CatalogSearch\Model\Advanced',
            array('addFilters', '__wakeup'),
            array(),
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
            array('view' => $view, 'request' => $request)
        );

        /** @var \Magento\CatalogSearch\Controller\Advanced\Result $instance */
        $instance = $objectManager->getObject(
            'Magento\CatalogSearch\Controller\Advanced\Result',
            array('context' => $context, 'catalogSearchAdvanced' => $catalogSearchAdvanced)
        );
        $instance->execute();
    }
}
