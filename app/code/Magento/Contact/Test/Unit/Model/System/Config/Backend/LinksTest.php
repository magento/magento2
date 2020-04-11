<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Model\System\Config\Backend;

use Magento\Contact\Model\System\Config\Backend\Links;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinksTest extends TestCase
{
    /**
     * @var Links|MockObject
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = (new ObjectManager($this))->getObject(
            Links::class
        );
    }

    public function testGetIdentities()
    {
        $this->assertTrue(is_array($this->_model->getIdentities()));
    }
}
