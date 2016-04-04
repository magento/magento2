<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurableAttributeSetHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConfigurableAttributeSetHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableAttributeSetHandler
     */
    private $configurableAttributeSetHandler;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->configurableAttributeSetHandler = $this->objectManagerHelper->getObject(
            ConfigurableAttributeSetHandler::class
        );
    }

    public function testModifyMeta()
    {
        $this->assertArrayHasKey(
            ConfigurableAttributeSetHandler::ATTRIBUTE_SET_HANDLER_MODAL,
            $this->configurableAttributeSetHandler->modifyMeta([])
        );
    }
}
