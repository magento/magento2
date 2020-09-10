<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Helper;

use Magento\Framework\Data\Wysiwyg\Normalizer;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Widget\Helper\Conditions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ConditionsTest
 *
 * PHPUnit test case for \Magento\Widget\Helper\Conditions
 */
class ConditionsTest extends TestCase
{
    /**
     * @var Conditions
     */
    protected $conditions;

    /**
     * @var Json|MockObject
     */
    private $serializer;

    /**
     * @var Normalizer|MockObject
     */
    private $normalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Json::class);
        $this->normalizer = $this->createMock(Normalizer::class);
        $this->conditions = (new ObjectManager($this))->getObject(
            Conditions::class,
            [
                'serializer' => $this->serializer,
                'normalizer' => $this->normalizer
            ]
        );
    }

    public function testEncodeDecode()
    {
        $value = ['string'];
        $serializedValue = 'serializedString';
        $normalizedValue = 'normalizedValue';
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($value)
            ->willReturn($serializedValue);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedValue)
            ->willReturn($value);
        $this->normalizer->expects($this->once())
            ->method('replaceReservedCharacters')
            ->with($serializedValue)
            ->willReturn($normalizedValue);
        $this->normalizer->expects($this->once())
            ->method('restoreReservedCharacters')
            ->with($normalizedValue)
            ->willReturn($serializedValue);
        $encoded = $this->conditions->encode($value);
        $this->assertEquals($value, $this->conditions->decode($encoded));
    }
}
