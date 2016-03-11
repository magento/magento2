<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product\Attribute\Source\Price;

use Magento\Bundle\Model\Product\Attribute\Source\Price\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class TypeTest
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Type
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(Type::class);
    }

    public function testGetAllOptions()
    {
        $this->assertEquals(
            [
                ['label' => __('Dynamic'), 'value' => 0],
                ['label' => __('Fixed'), 'value' => 1],
            ],
            $this->model->getAllOptions()
        );
    }

    public function testGetOptionText()
    {
        $this->assertEquals(__('Dynamic'), $this->model->getOptionText(0));
    }

    public function testGetOptionTextToBeFalse()
    {
        $this->assertFalse($this->model->getOptionText(2));
    }
}
