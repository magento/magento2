<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Controller\Cookie;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Prolong cookie action test class.
 * @covers \Magento\PageCache\Controller\Cookie\Prolong
 */
class ProlongTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\PageCache\Model\Cookie\Prolongation\Frontend|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontendCookieProlongationMock;
    /**
     * @var \Magento\PageCache\Controller\Cookie\Prolong
     */
    protected $_action;

    /**
     * Initial setup.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_frontendCookieProlongationMock = $this->createMock(
            \Magento\PageCache\Model\Cookie\Prolongation\Frontend::class
        );

        $this->_action = (new ObjectManager($this))->getObject(
            \Magento\PageCache\Controller\Cookie\Prolong::class,
            [
                'frontendCookieProlongation' => $this->_frontendCookieProlongationMock
            ]
        );
    }

    /**
     * Tests execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->_frontendCookieProlongationMock
            ->expects($this->once())
            ->method('execute');

        $this->_action->execute();
    }
}