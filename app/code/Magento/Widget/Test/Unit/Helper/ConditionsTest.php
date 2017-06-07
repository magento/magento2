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
class ConditionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var Normalizer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $normalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->serializer = $this->getMock(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->normalizer = $this->getMock(Normalizer::class);
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
