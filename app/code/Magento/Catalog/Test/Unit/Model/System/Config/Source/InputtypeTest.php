<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\System\Config\Source;

use Magento\Catalog\Model\System\Config\Source\Inputtype;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class InputtypeTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_helper;

    /**
     * @var Inputtype
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_helper = new ObjectManager($this);
        $this->_model = $this->_helper->getObject(Inputtype::class);
    }

    public function testToOptionArrayIsArray()
    {
        $this->assertIsArray($this->_model->toOptionArray());
    }

    public function testToOptionArrayValid()
    {
        $expects = [
            ['value' => 'multiselect', 'label' => 'Multiple Select'],
            ['value' => 'select', 'label' => 'Dropdown'],
        ];
        $this->assertEquals($expects, $this->_model->toOptionArray());
    }
}
