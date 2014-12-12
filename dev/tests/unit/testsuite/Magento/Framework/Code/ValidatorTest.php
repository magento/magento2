<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Code;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Validator
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new Validator();
    }

    public function testValidate()
    {
        $className = 'Same\Class\Name';
        $validator1 = $this->getMock('Magento\Framework\Code\ValidatorInterface');
        $validator1->expects($this->once())->method('validate')->with($className);
        $validator2 = $this->getMock('Magento\Framework\Code\ValidatorInterface');
        $validator2->expects($this->once())->method('validate')->with($className);

        $this->model->add($validator1);
        $this->model->add($validator2);
        $this->model->validate($className);
    }
}
