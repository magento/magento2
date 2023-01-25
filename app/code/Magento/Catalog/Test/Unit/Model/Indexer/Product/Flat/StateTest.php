<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat;

use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Indexer;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var State
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $indexerMock = $this->createMock(Indexer::class);
        $flatIndexerHelperMock = $this->createMock(\Magento\Catalog\Helper\Product\Flat\Indexer::class);
        $configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_model = $this->_objectManager->getObject(
            State::class,
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
        $this->assertInstanceOf(
            \Magento\Catalog\Helper\Product\Flat\Indexer::class,
            $this->_model->getFlatIndexerHelper()
        );
    }
}
