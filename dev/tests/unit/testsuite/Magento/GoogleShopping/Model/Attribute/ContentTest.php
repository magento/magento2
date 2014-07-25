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
namespace Magento\GoogleShopping\Model\Attribute;

class ContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider convertAttributeDataProvider
     * @param int|null $attributeId
     * @param string $description
     * @param string $mapValue
     */
    public function testConvertAttribute($attributeId, $description, $mapValue)
    {
        $product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            array('getDescription', '__wakeup'),
            array(),
            '',
            false
        );
        $product->expects($this->any())->method('getDescription')->will($this->returnValue($description));

        $defaultFrontend = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend',
            array('getValue'),
            array(),
            '',
            false
        );
        $defaultFrontend->expects($this->any())
            ->method('getValue')
            ->with($product)
            ->will($this->returnValue($mapValue));

        $attribute = $this->getMock(
            '\Magento\Catalog\Model\Entity\Attribute',
            array('getFrontend', '__wakeup'),
            array(),
            '',
            false
        );
        $attribute->expects($this->any())->method('getFrontend')->will($this->returnValue($defaultFrontend));

        $productHelper = $this->getMock(
            '\Magento\GoogleShopping\Helper\Product',
            array('getProductAttribute'),
            array(),
            '',
            false
        );
        $productHelper->expects($this->any())
            ->method('getProductAttribute')
            ->with($product, $attributeId)
            ->will($this->returnValue($attribute));


        $googleShoppingHelper = $this->getMock(
            '\Magento\GoogleShopping\Helper\Data',
            array('cleanAtomAttribute'),
            array(),
            '',
            false
        );
        $googleShoppingHelper->expects($this->once())
            ->method('cleanAtomAttribute')
            ->with($mapValue)
            ->will($this->returnValue($mapValue));

        $model = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject(
                '\Magento\GoogleShopping\Model\Attribute\Content',
                array('gsProduct' => $productHelper, 'googleShoppingHelper' => $googleShoppingHelper)
            );

        $service = $this->getMock('Zend_Gdata_App', array('newContent', 'setText'), array(), '', false);
        $service->expects($this->once())->method('newContent')->will($this->returnSelf());
        $service->expects($this->once())->method('setText')->with($mapValue)->will($this->returnValue($mapValue));

        $entry = $this->getMock(
            '\Magento\Framework\Gdata\Gshopping\Entry',
            array('getService', 'setContent'),
            array(),
            '',
            false
        );
        $entry->expects($this->once())->method('getService')->will($this->returnValue($service));
        $entry->expects($this->once())->method('setContent')->with($mapValue);

        $groupAttributeDescription = $this->getMock(
            '\Magento\GoogleShopping\Model\Attribute\DefaultAttribute',
            array(),
            array(),
            '',
            false
        );

        $model->setGroupAttributeDescription($groupAttributeDescription);
        $model->setAttributeId($attributeId);

        $this->assertEquals($entry, $model->convertAttribute($product, $entry));
    }

    /**
     * @return array
     */
    public function convertAttributeDataProvider()
    {
        return array(
            array(1, 'description', 'short description'),
            array(null, 'description', 'description'),
        );
    }
}
