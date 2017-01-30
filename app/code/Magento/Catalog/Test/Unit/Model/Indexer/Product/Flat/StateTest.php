<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $indexerMock = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $flatIndexerHelperMock = $this->getMock(
            'Magento\Catalog\Helper\Product\Flat\Indexer',
            [],
            [],
            '',
            false
        );
        $configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_model = $this->_objectManager->getObject(
            'Magento\Catalog\Model\Indexer\Product\Flat\State',
            [
                'scopeConfig' => $configMock,
                'flatIndexer' => $indexerMock,
                'flatIndexerHelper' => $flatIndexerHelperMock,
                false
            ]
        );
    }

    public function testGetIndexer()
    {
        $this->assertInstanceOf('\Magento\Catalog\Helper\Product\Flat\Indexer', $this->_model->getFlatIndexerHelper());
    }
}
