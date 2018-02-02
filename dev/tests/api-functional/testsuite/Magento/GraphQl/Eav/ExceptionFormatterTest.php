<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Eav;

use Magento\Framework\App\State;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ExceptionFormatterTest extends GraphQlAbstract
{
    /** @var  string */
    private $mageMode;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->mageMode = $this->objectManager->get(State::class)->getMode();
    }

    protected function tearDown()
    {
        $this->objectManager->get(State::class)->setMode($this->mageMode);
    }

    /**
     * @param string $mageMode
     */
    private function setDeveloperMode($mageMode = State::MODE_DEVELOPER)
    {
        $this->objectManager->get(State::class)->setMode($mageMode);
        echo 'mageMode:'. $mageMode;
    }

    public function testInvalidEntityTypeExceptionInDeveloperMode($mageMode = State::MODE_DEVELOPER)
    {
        $this->markTestSkipped(
            "Current infrastructure cannot switch out of produciton mode, which is required for this test."
        );
        $this->setDeveloperMode();
        $this->objectManager->get(State::class)->setMode($mageMode);

        if (!$this->cleanCache()) {
            $this->fail('Cache could not be cleaned properly.');
        }
        $query
            = <<<QUERY
  {
  customAttributeMetadata(attributes:[
    {
      attribute_code:"sku"
      entity_type:"invalid"
    }
  ])
    {
      items{        
      attribute_code
      attribute_type
      entity_type
    }      
    }  
  }
QUERY;
        $this->expectException(\Exception::class);

        $this->expectExceptionMessage('Invalid entity_type specified: invalid');

        $this->graphQlQuery($query);
    }
}
