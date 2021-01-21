<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Data\Wysiwyg\Normalizer;

/**
 * Class ConditionsTest
 */
class ConditionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    /**
     * @var Normalizer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $normalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->serializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->normalizer = $this->createMock(Normalizer::class);
        $this->conditions = (new ObjectManager($this))->getObject(
            \Magento\Widget\Helper\Conditions::class,
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
