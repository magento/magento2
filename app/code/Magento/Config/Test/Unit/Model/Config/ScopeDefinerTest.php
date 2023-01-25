<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopeDefinerTest extends TestCase
{
    /**
     * @var ScopeDefiner
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    protected function setUp(): void
    {
        $this->_requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(
            ScopeDefiner::class,
            ['request' => $this->_requestMock]
        );
    }

    public function testGetScopeReturnsDefaultScopeIfNoScopeDataIsSpecified()
    {
        $this->assertEquals(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $this->_model->getScope());
    }

    public function testGetScopeReturnsStoreScopeIfStoreIsSpecified()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->willReturnMap(
            [['website', null, 'someWebsite'], ['store', null, 'someStore']]
        );
        $this->assertEquals(ScopeInterface::SCOPE_STORE, $this->_model->getScope());
    }

    public function testGetScopeReturnsWebsiteScopeIfWebsiteIsSpecified()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->willReturnMap(
            [['website', null, 'someWebsite'], ['store', null, null]]
        );
        $this->assertEquals(ScopeInterface::SCOPE_WEBSITE, $this->_model->getScope());
    }
}
