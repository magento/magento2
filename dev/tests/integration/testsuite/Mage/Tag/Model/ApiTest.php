<?php
/**
 * Product tag API test.
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
 * @magentoDataFixture Mage/Tag/Model/Api/_files/tag.php
 */
class Mage_Tag_Model_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test info method.
     */
    public function testInfo()
    {
        $tagName = 'tag_name';
        $tagStatus = Mage_Tag_Model_Tag::STATUS_APPROVED;
        /** @var Mage_Tag_Model_Tag $tag */
        $tag = Mage::getModel('Mage_Tag_Model_Tag');
        $tagId = $tag->loadByName($tagName)->getTagId();
        /** Retrieve tag info. */
        $tagInfo = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductTagInfo',
            array($tagId)
        );
        /** Assert response is not empty. */
        $this->assertNotEmpty($tagInfo, 'Tag info is not retrieved.');
        /** Assert base fields are present in the response. */
        $expectedFields = array('status', 'name', 'base_popularity', 'products');
        $missingFields = array_diff($expectedFields, array_keys($tagInfo));
        $this->assertEmpty(
            $missingFields,
            sprintf("The following fields must be present in response: %s.", implode(', ', $missingFields))
        );
        /** Assert retrieved tag data is correct. */
        $this->assertEquals($tagInfo->name, $tagName, 'Tag name is incorrect.');
        $this->assertEquals($tagInfo->status, $tagStatus, 'Tag status is incorrect.');
    }

    /**
     * Test update method.
     */
    public function testUpdate()
    {
        /** @var Mage_Tag_Model_Tag $tag */
        $tagId = Mage::getModel('Mage_Tag_Model_Tag')->loadByName('tag_name')->getTagId();
        $updateData = array('name' => 'new_tag_name', 'status' => Mage_Tag_Model_Tag::STATUS_DISABLED);
        /** Update tag. */
        $tagUpdateResponse = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductTagUpdate',
            array($tagId, (object)$updateData)
        );
        /** Check tag update result. */
        $this->assertTrue((bool)$tagUpdateResponse, 'Tag update was unsuccessful.');
        /** Assert updated fields. */
        /** @var Mage_Tag_Model_Tag $updatedTag */
        $updatedTag = Mage::getModel('Mage_Tag_Model_Tag')->loadByName($updateData['name']);
        $this->assertNotEmpty($updatedTag->getTagId(), 'Tag name update was unsuccessful.');
        $this->assertEquals($updateData['status'], $updatedTag->getStatus(), 'Tag status update was unsuccessful.');
    }
}
