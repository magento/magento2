<?php
/**
 * Downloadable product links API model test.
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Catalog_Model_Product_Api_DownloadableLinkCRUDTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test downloadable link create
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/LinkCRUD.php
     */
    public function testDownloadableLinkCreate()
    {
        $tagFixture = simplexml_load_file(dirname(__FILE__) . '/_files/_data/xml/LinkCRUD.xml');
        $items = Magento_Test_Helper_Api::simpleXmlToArray($tagFixture->items);

        $productId = Mage::registry('productData')->getId();

        foreach ($items as $item) {
            foreach ($item as $key => $value) {
                if ($value['type'] == 'file') {
                    $filePath = dirname(__FILE__) . '/_files/_data/files/' . $value['file']['filename'];
                    $value['file'] = array(
                        'name' => str_replace('/', '_', $value['file']['filename']),
                        'base64_content' => base64_encode(file_get_contents($filePath)),
                        'type' => $value['type']
                    );
                }
                if ($key == 'link' && $value['sample']['type'] == 'file') {
                    $filePath = dirname(__FILE__) . '/_files/_data/files/' . $value['sample']['file']['filename'];
                    $value['sample']['file'] = array(
                        'name' => str_replace('/', '_', $value['sample']['file']['filename']),
                        'base64_content' => base64_encode(file_get_contents($filePath))
                    );
                }

                $resultId = Magento_Test_Helper_Api::call(
                    $this,
                    'catalogProductDownloadableLinkAdd',
                    array(
                        'productId' => $productId,
                        'resource' => $value,
                        'resourceType' => $key
                    )
                );
                $this->assertGreaterThan(0, $resultId);
            }
        }
    }

    /**
     * Test get downloadable link items
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/DownloadableWithLinks.php
     */
    public function testDownloadableLinkItems()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('downloadable');
        $productId = $product->getId();

        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductDownloadableLinkList',
            array('productId' => $productId)
        );
        /** @var Mage_Downloadable_Model_Product_Type $downloadable */
        $downloadable = $product->getTypeInstance();
        $links = $downloadable->getLinks($product);

        $this->assertEquals(count($links), count($result['links']));
        foreach ($result['links'] as $actualLink) {
            foreach ($links as $expectedLink) {
                if ($actualLink['link_id'] == $expectedLink) {
                    $this->assertEquals($expectedLink->getData('title'), $actualLink['title']);
                    $this->assertEquals($expectedLink->getData('price'), $actualLink['price']);
                }
            }
        }
    }

    /**
     * Remove downloadable link
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/DownloadableWithLinks.php
     */
    public function testDownloadableLinkRemove()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('downloadable');
        /** @var Mage_Downloadable_Model_Product_Type $downloadable */
        $downloadable = $product->getTypeInstance();
        $links = $downloadable->getLinks($product);
        foreach ($links as $link) {
            $removeResult = Magento_Test_Helper_Api::call(
                $this,
                'catalogProductDownloadableLinkRemove',
                array(
                    'linkId' => $link->getId(),
                    'resourceType' => 'link'
                )
            );
            $this->assertTrue((bool)$removeResult);
        }
    }
}
