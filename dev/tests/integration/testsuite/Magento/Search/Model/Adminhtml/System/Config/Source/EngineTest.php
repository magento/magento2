<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Adminhtml\System\Config\Source;

/**
 * @magentoAppArea adminhtml
 */
class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\Adminhtml\System\Config\Source\Engine
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Search\Model\Adminhtml\System\Config\Source\Engine'
        );
    }

    public function testToOptionArray()
    {
        $options = $this->_model->toOptionArray();
        $this->assertNotEmpty($options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('value', $option);
        }
    }
}
