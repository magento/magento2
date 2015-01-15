<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Framework\Object;
use Magento\TestFramework\Helper\ObjectManager;

class CustomlayoutupdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $attributeName = 'private';

    /**
     * @var \Magento\Catalog\Model\Attribute\Backend\Customlayoutupdate
     */
    private $model;

    /**
     * @expectedException \Magento\Eav\Model\Entity\Attribute\Exception
     */
    public function testValidateException()
    {
        $object = new Object();
        $object->setData($this->attributeName, 'exception');
        $this->model->validate($object);
    }

    /**
     * @param string
     * @dataProvider validateProvider
     */
    public function testValidate($data)
    {
        $object = new Object();
        $object->setData($this->attributeName, $data);

        $this->assertTrue($this->model->validate($object));
        $this->assertTrue($this->model->validate($object));
    }

    /**
     * @return array
     */
    public function validateProvider()
    {
        return [[''], ['xml']];
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            '\Magento\Catalog\Model\Attribute\Backend\Customlayoutupdate',
            [
                'layoutUpdateValidatorFactory' => $this->getMockedLayoutUpdateValidatorFactory()
            ]
        );
        $this->model->setAttribute($this->getMockedAttribute());
    }

    /**
     * @return \Magento\Core\Model\Layout\Update\ValidatorFactory
     */
    private function getMockedLayoutUpdateValidatorFactory()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Core\Model\Layout\Update\ValidatorFactory');
        $mockBuilder->disableOriginalConstructor();
        $mockBuilder->setMethods(['create']);
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getMockedValidator()));

        return $mock;
    }

    /**
     * @return \Magento\Core\Model\Layout\Update\Validator
     */
    private function getMockedValidator()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Core\Model\Layout\Update\Validator');
        $mockBuilder->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('isValid')
            ->will(
                /**
                 * @param string $xml
                 * $return bool
                 */
                $this->returnCallback(
                    function ($xml) {
                        if ($xml == 'exception') {
                            return false;
                        } else {
                            return true;
                        }
                    }
                )
            );

        $mock->expects($this->any())
            ->method('getMessages')
            ->will($this->returnValue(['error']));

        return $mock;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private function getMockedAttribute()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\AbstractAttribute');
        $mockBuilder->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($this->attributeName));

        $mock->expects($this->any())
            ->method('getIsRequired')
            ->will($this->returnValue(false));

        return $mock;
    }
}
