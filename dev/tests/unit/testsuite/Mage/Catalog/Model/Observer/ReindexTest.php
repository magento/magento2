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
 * @category    Magento
 * @package     Mage_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for Catalog Observer Reindex
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Observer_ReindexTest extends PHPUnit_Framework_TestCase
{
    /**
     * Positive test for fulltext reindex
     */
    public function testFulltextReindex()
    {
        $affectedProduct = array(1, 2, 3);

        $fulltextReindex = $this->getMock(
            'Mage_CatalogSearch_Model_Resource_Fulltext',
            array('rebuildIndex'),
            array(),
            '',
            false
        );
        $fulltextReindex->expects($this->once())
            ->method('rebuildIndex')
            ->with(
                $this->logicalOr(
                    $this->equalTo(null),
                    $this->equalTo($affectedProduct)
                )
            );

        $objectManager = $this->getMock(
            'Magento_ObjectManager_ObjectManager',
            array('get'),
            array(),
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('get')
            ->with('Mage_CatalogSearch_Model_Resource_Fulltext')
            ->will($this->returnValue($fulltextReindex));

        $observer = new Varien_Event_Observer(
            array(
                'data_object' => new Varien_Object(
                    array('affected_product_ids' => $affectedProduct)
                )
            )
        );

        /** @var $objectManager Magento_ObjectManager */
        $object = new Mage_Catalog_Model_Observer_Reindex($objectManager);
        $this->assertInstanceOf('Mage_Catalog_Model_Observer_Reindex', $object->fulltextReindex($observer));
    }
}
