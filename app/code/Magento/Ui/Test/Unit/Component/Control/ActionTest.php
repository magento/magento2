<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Ui\Component\Control\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ActionTest
 */
class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Action
     */
    protected $action;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->action = $this->objectManager->getObject('Magento\Ui\Component\Control\Action');
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->assertTrue($this->action->getComponentName() === Action::NAME);
    }
}
