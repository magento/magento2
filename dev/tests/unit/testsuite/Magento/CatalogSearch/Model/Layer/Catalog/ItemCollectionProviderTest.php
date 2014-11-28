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

namespace Magento\CatalogSearch\Model\Layer\Catalog;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ItemCollectionProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCollection()
    {
        $categoryMock = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);

        $collectionMock = $this->getMock('Magento\Catalog\Model\Resource\Product\Collection', [], [], '', false);
        $collectionMock->expects($this->once())->method('addCategoryFilter')->with($categoryMock);

        $collectionFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $collectionFactoryMock->expects($this->any())->method('create')->will($this->returnValue($collectionMock));

        $objectManager = new ObjectManagerHelper($this);
        $provider = $objectManager->getObject(
            'Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider',
            ['collectionFactory' => $collectionFactoryMock]
        );

        $provider->getCollection($categoryMock);
    }
}
