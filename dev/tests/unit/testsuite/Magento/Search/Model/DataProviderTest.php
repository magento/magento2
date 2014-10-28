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

namespace Magento\Search\Model;

use Magento\TestFramework\Helper\ObjectManager;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testGetRecommendations()
    {
        /** @var \Magento\Search\Model\QueryInterface|\PHPUnit_Framework_MockObject_MockObject $searchQuery */
        $searchQuery = $this->getMockBuilder('\Magento\Search\Model\QueryInterface')->getMockForAbstractClass();
        /** @var \Magento\Search\Model\SearchDataProvider $dataProvider */
        $dataProvider = $this->objectManager->getObject('\Magento\Search\Model\SearchDataProvider');
        $this->assertEquals([], $dataProvider->getSearchData($searchQuery));
    }

    public function testIsCountResultsEnabled()
    {
        /** @var \Magento\Search\Model\SearchDataProvider $dataProvider */
        $dataProvider = $this->objectManager->getObject('\Magento\Search\Model\SearchDataProvider');
        $this->assertFalse($dataProvider->isCountResultsEnabled());
    }
}
