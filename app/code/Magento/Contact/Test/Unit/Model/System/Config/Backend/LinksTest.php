<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Model\System\Config\Backend;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class LinksTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Contact\Model\System\Config\Backend\Links|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = (new ObjectManager($this))->getObject(
            \Magento\Contact\Model\System\Config\Backend\Links::class
        );
    }

    public function testGetIdentities()
    {
        $this->assertIsArray($this->_model->getIdentities());
    }
}
