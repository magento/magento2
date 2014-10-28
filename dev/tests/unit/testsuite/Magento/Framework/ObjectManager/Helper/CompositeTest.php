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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\ObjectManager\Helper;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeHelper
     */
    protected $compositeHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->compositeHelper = $this->objectManager->getObject('Magento\Framework\ObjectManager\Helper\Composite');
    }

    public function testFilterAndSortDeclaredComponents()
    {
        $firstComponent = new \Magento\Framework\Object();
        $secondComponent = new \Magento\Framework\Object();
        $thirdComponent = new \Magento\Framework\Object();
        $contexts = [
            [
                'type' => new \Magento\Framework\Object(),
            ],
            [
                'sortOrder' => 50,
            ],
            [
                'sortOrder' => 20,
                'type' => $firstComponent
            ],
            [
                'sortOrder' => 30,
                'type' => $secondComponent,
            ],
            [
                'sortOrder' => 10,
                'type' => $thirdComponent
            ],
        ];

        $result = $this->compositeHelper->filterAndSortDeclaredComponents($contexts);

        /** Ensure that misconfigured components were filtered out correctly */
        $this->assertCount(3, $result, 'Misconfigured components filtration does not work as expected.');

        /** Verify that components were ordered according to the defined sort order */
        $incorrectSortingMessage = "Registered components were sorted incorrectly";
        $this->assertSame($thirdComponent, $result[0]['type'], $incorrectSortingMessage);
        $this->assertSame($firstComponent, $result[1]['type'], $incorrectSortingMessage);
        $this->assertSame($secondComponent, $result[2]['type'], $incorrectSortingMessage);
    }
}
