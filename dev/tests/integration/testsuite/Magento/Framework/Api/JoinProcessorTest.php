<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

class JoinProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $extensionConfigFileResolverMock = $this->getMockBuilder('Magento\Framework\Config\FileResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $extensionConfigFilePath = __DIR__ . '/_files/extension_attributes.xml';
        $extensionConfigFileContent = file_get_contents($extensionConfigFilePath);
        $extensionConfigFileResolverMock->expects($this->any())
            ->method('get')
            ->willReturn([$extensionConfigFilePath => $extensionConfigFileContent]);
        $configReader = $objectManager->create(
            'Magento\Framework\Api\Config\Reader',
            ['fileResolver' => $extensionConfigFileResolverMock]
        );
        /** @var \Magento\Framework\Api\JoinProcessor $joinProcessor */
        $joinProcessor = $objectManager->create(
            'Magento\Framework\Api\JoinProcessor',
            ['configReader' => $configReader]
        );
        $productInterface = 'Magento\Catalog\Api\Data\ProductInterface';
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
        $collection = $objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');

        $joinProcessor->process($collection, $productInterface);

        $expectedSql = <<<EXPECTED_SQL
SELECT `e`.*,
     `cataloginventory_stock_item`.`qty` AS `extension_attribute_stock_item_qty`,
     `reviews`.`comment` AS `extension_attribute_reviews_comment`,
     `reviews`.`rating` AS `extension_attribute_reviews_rating`,
     `reviews`.`date` AS `extension_attribute_reviews_date` FROM `catalog_product_entity` AS `e`
 LEFT JOIN `cataloginventory_stock_item` AS `extension_attribute_stock_item` ON e.id = extension_attribute_stock_item.id
 LEFT JOIN `reviews` AS `extension_attribute_reviews` ON e.id = extension_attribute_reviews.product_id
EXPECTED_SQL;
        $resultSql = $collection->getSelectSql(true);
        $formattedResultSql = str_replace(',', ",\n    ", $resultSql);
        $this->assertEquals($expectedSql, $formattedResultSql);
    }
}
