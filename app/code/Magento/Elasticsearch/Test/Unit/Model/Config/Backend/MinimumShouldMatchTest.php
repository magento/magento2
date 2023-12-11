<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Config\Backend;

use Magento\Elasticsearch\Model\Config\Backend\MinimumShouldMatch;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Test elasticsearch minimum should match data model
 */
class MinimumShouldMatchTest extends TestCase
{
    /**
     * @var MinimumShouldMatch
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(MinimumShouldMatch::class);
        parent::setUp();
    }

    /**
     * @param string $value
     * @param bool $valid
     * @dataProvider validateValueDataProvider
     * @throws LocalizedException
     */
    public function testValidateValue(string $value, bool $valid)
    {
        $this->model->setValue($value);
        try {
            $this->model->validateValue();
        } catch (Throwable $exception) {
            $this->assertFalse($valid);
            return;
        }
        $this->assertTrue($valid);
    }

    /**
     * @return array
     */
    public function validateValueDataProvider(): array
    {
        return  [
            ['3', true],
            ['-2', true],
            ['75%', true],
            ['-25%', true],
            ['3<90%', true],
            ['2<-25% 9<-3', true],
            ['90%<3', false],
            ['<90%', false],
            ['90%<', false],
            ['-3<2', false],
            ['two', false],
            ['2<', false],
            ['<2', false],
        ];
    }
}
