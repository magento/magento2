<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\Request;

use Magento\CatalogSearch\Model\Search\Request\SearchModifier;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test search requests modifier
 */
class SearchModifierTest extends TestCase
{
    /**
     * @var RequestGenerator|MockObject
     */
    private $requestGenerator;

    /**
     * @var SearchModifier
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->requestGenerator = $this->createMock(RequestGenerator::class);
        $this->model = new SearchModifier($this->requestGenerator);
    }

    /**
     * Test that the result is merged into the initial requests
     */
    public function testModifier(): void
    {
        $requests = ['a' => ['x', 'y'], 'b' => ['k']];
        $expected = ['a' => ['x', 'y', 'z'], 'b' => ['k'], 'c' => ['n']];
        $this->requestGenerator->expects($this->once())
            ->method('generate')
            ->willReturn(['a' => ['z'], 'c' => ['n']]);
        $this->assertEquals($expected, $this->model->modify($requests));
    }
}
