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

namespace Magento\Framework\View\Page\Config;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for page config structure model
 */
class StructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Structure
     */
    protected $structure;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->structure = $objectManagerHelper->getObject(
            'Magento\Framework\View\Page\Config\Structure'
        );
    }

    public function testSetElementAttribute()
    {
        $elementName1 = 'elementName1';
        $attributeName1 = 'attributeName1';
        $attributeValue1 = 'attributeValue1';

        $elementName2 = 'elementName2';
        $attributeName2 = 'attributeName2';
        $attributeValue2 = 'attributeValue2';

        $expected = [
            'elementName1' => ['attributeName1' => 'attributeValue1'],
            'elementName2' => ['attributeName2' => 'attributeValue2']
        ];

        $this->structure->setElementAttribute($elementName1, $attributeName1, $attributeValue1);
        $this->structure->setElementAttribute($elementName2, $attributeName2, $attributeValue2);
        $this->assertEquals($expected, $this->structure->getElementAttributes());

        $expectedAfterRemove =[
            'elementName2' => ['attributeName2' => 'attributeValue2']
        ];
        $this->structure->setElementAttribute($elementName1, $attributeName1, false);
        $this->structure->processRemoveElementAttributes();
        $this->assertEquals($expectedAfterRemove, $this->structure->getElementAttributes());
    }

    public function testSetBodyClass()
    {
        $class1 = 'class_1';
        $class2 = 'class_2';
        $expected = [$class1, $class2];
        $this->structure->setBodyClass($class1);
        $this->structure->setBodyClass($class2);
        $this->assertEquals($expected, $this->structure->getBodyClasses());

        $this->structure->setBodyClass('');
        $this->assertEmpty($this->structure->getBodyClasses());
    }

    public function testTitle()
    {
        $data = 'test';
        $this->structure->setTitle($data);
        $this->assertEquals($data, $this->structure->getTitle());
    }

    public function testMetadata()
    {
        $metadataName = 'name';
        $metadataContent = 'content';
        $expected = [$metadataName => $metadataContent];

        $this->structure->setMetadata($metadataName, $metadataContent);

        $this->assertEquals($expected, $this->structure->getMetadata());
    }

    public function testAssets()
    {
        $assetName = 'test';
        $assetAttributes = ['attr1', 'attr2'];
        $expected = [$assetName => $assetAttributes];

        $this->structure->addAssets($assetName, $assetAttributes);
        $this->assertEquals($expected, $this->structure->getAssets());
    }

    public function testProcessRemoveAssets()
    {
        $assetName1 = 'test1';
        $assetAttributes1 = ['attr1_1', 'attr1_2'];

        $assetName2 = 'test2';
        $assetAttributes2 = ['attr2_1', 'attr2_2'];

        $expected = [$assetName1 => $assetAttributes1];

        $this->structure->addAssets($assetName1, $assetAttributes1);
        $this->structure->addAssets($assetName2, $assetAttributes2);
        $this->structure->removeAssets($assetName2);
        $this->structure->processRemoveAssets();
        $this->assertEquals($expected, $this->structure->getAssets());
    }
}
