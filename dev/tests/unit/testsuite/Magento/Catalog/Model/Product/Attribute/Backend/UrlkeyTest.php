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
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Framework\Object;
use Magento\TestFramework\Helper\ObjectManager;

class UrlkeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Urlkey
     */
    private $model;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    protected function setUp()
    {

        $this->attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods(['__wakeup', 'getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject('Magento\Catalog\Model\Product\Attribute\Backend\Urlkey');
        $this->model->setAttribute($this->attribute);
    }

    /**
     * @param bool|string $objData
     * @dataProvider beforeSaveProvider
     */
    public function testBeforeSave($objData)
    {
        $attributeName = 'attr';
        $this->attribute->expects($this->once())->method('getName')->will($this->returnValue($attributeName));

        /** @var \Magento\Catalog\Model\Product\Url|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder('Magento\Catalog\Model\Product\Url')
            ->setMethods(['getName', 'getData', 'setData', 'formatUrlKey'])
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->once())->method('getData')->with($this->equalTo($attributeName))->will(
            $this->returnValue($objData)
        );
        $object->expects($this->any())->method('getName')->will($this->returnValue('testData'));
        $object->expects($this->any())->method('setData')->with(
            $this->equalTo($attributeName),
            $this->logicalOr($this->equalTo('testData'), $this->equalTo('someData'))
        );
        $object->expects($this->any())->method('formatUrlKey')->with(
            $this->logicalOr($this->equalTo('testData'), $this->equalTo('someData'))
        )->will(
                $this->returnCallback(
                    function ($data) {
                        return $data;
                    }
                )
            );

        $this->model->beforeSave($object);
    }

    public function beforeSaveProvider()
    {
        return [[false], [''], ['someData']];
    }
} 