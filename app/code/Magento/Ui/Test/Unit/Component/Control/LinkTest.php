<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Ui\Component\Control\Link;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class LinkTest
 */
class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Link
     */
    protected $link;

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

        $this->link = $this->objectManager->getObject('Magento\Ui\Component\Control\Link');
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->assertTrue($this->link->getComponentName() === Link::NAME);
    }
}
