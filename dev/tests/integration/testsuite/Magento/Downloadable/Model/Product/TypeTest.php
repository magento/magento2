<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Downloadable\Model\Product\Type
 */
namespace Magento\Downloadable\Model\Product;

/**
 * Test for \Magento\Downloadable\Model\Product\Type
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Downloadable\Model\Product\Type'
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoAppArea adminhtml
     */
    public function testDeleteTypeSpecificData()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        $product->setOrigData();
        $downloadableData = [];

        $links = $this->_model->getLinks($product);
        $this->assertNotEmpty($links);
        $samples = $this->_model->getSamples($product);
        $this->assertNotEmpty($samples->getData());
        /** @var \Magento\Downloadable\Model\Link $link */
        foreach ($links as $link) {
            $data = $link->getData();
            $data['title'] = 'UPDATED . ' . $data['title'];
            $downloadableData['link'][] = $data;
        }
        foreach ($samples as $sample) {
            $downloadableData['sample'][] = $sample->getData();
        }

        $product->setDownloadableData($downloadableData);
        $this->_model->deleteTypeSpecificData($product);
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);

        $links = $this->_model->getLinks($product);
        $this->assertEmpty($links);
        $samples = $this->_model->getSamples($product);
        $this->assertEmpty($samples->getData());
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoAppArea adminhtml
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveTypeSpecificData()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        $product->setOrigData();
        $downloadableData = [];

        $links = $this->_model->getLinks($product);
        $this->assertNotEmpty($links);
        $samples = $this->_model->getSamples($product);
        $this->assertNotEmpty($samples->getData());
        $i=0;
        foreach ($links as $link) {
            $i++;
            $linkData = $link->getData();
            $linkData['is_delete'] = 0;
            $linkData['type'] = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            $linkData['title'] = 'Updated downloadable link #' . $i;
            $downloadableData['link'][] = $linkData;
        }
        $i=0;
        foreach ($samples as $sample) {
            $i++;
            $sampleData = $sample->getData();
            $sampleData['is_delete'] = 0;
            $sampleData['type'] = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            $sampleData['title'] = 'Updated downloadable sample #' . $i;
            $downloadableData['sample'][] = $sampleData;
        }

        $product->setDownloadableData($downloadableData);
        $this->_model->save($product);

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);

        $expectedLink = [
            'default_price' => null,
            'default_title' => null,
            'is_shareable' => '2',
            'link_file' => null,
            'link_type' => 'file',
            'link_url' => null,
            'number_of_downloads' => '15',
            'price' => '15.0000',
            'product' => $product,
            'product_id' => 1,
            'sample_file' => null,
            'sample_type' => 'file',
            'sample_url' => null,
            'sort_order' => '0',
            'store_title' => 'Updated downloadable link #1',
            'title' => 'Updated downloadable link #1',
            'website_price' => '15.0000',
        ];
        $expectedExtensionAttributes = [
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'admin@example.com'
        ];
        $links = $this->_model->getLinks($product);
        $this->assertNotEmpty($links);
        $this->assertCount(1, $links);
        /** @var \Magento\Downloadable\Model\Link $link */
        $link = reset($links);
        /** @var \Magento\User\Api\Data\UserInterface $testAttribute */
        $testAttribute = $link->getExtensionAttributes()->getTestAttribute();
        $this->assertEquals($expectedExtensionAttributes['firstname'], $testAttribute->getFirstName());
        $this->assertEquals($expectedExtensionAttributes['lastname'], $testAttribute->getLastName());
        $this->assertEquals($expectedExtensionAttributes['email'], $testAttribute->getEmail());
        foreach ($expectedLink as $key => $value) {
            $this->assertTrue($link->hasData($key), 'Key ' . $key . ' not exist!');
            $this->assertArrayHasKey($key, $link);
            $this->assertEquals($value, $link->getData($key));
        }

        $expectedSample = [
            'default_title' => null,
            'product_id' => '1',
            'sample_file' => null,
            'sample_type' => 'file',
            'sample_url' => null,
            'sort_order' => '0',
            'store_title' => 'Updated downloadable sample #1',
            'title' => 'Updated downloadable sample #1',
        ];

        $samples = $this->_model->getSamples($product);
        $this->assertNotEmpty($samples->getData());
        $this->assertEquals(1, $samples->count());
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $samples->getFirstItem()->getData();
        /** @var \Magento\User\Api\Data\UserInterface $testAttribute */
        $testAttribute = $sample['extension_attributes']->getTestAttribute();
        $this->assertEquals($expectedExtensionAttributes['firstname'], $testAttribute->getFirstName());
        $this->assertEquals($expectedExtensionAttributes['lastname'], $testAttribute->getLastName());
        $this->assertEquals($expectedExtensionAttributes['email'], $testAttribute->getEmail());
        foreach ($expectedSample as $key => $value) {
            $this->assertArrayHasKey($key, $sample);
            $this->assertEquals($value, $sample[$key]);
        }
    }
}
