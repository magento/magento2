<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\EncodedContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

<<<<<<< HEAD
/**
 * Class EncodedContextTest
 */
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class EncodedContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param string $content
     * @param string|null $initializationVector
     * @return void
     * @dataProvider constructDataProvider
     */
    public function testConstruct($content, $initializationVector)
    {
        $constructorArguments = [
            'content' => $content,
            'initializationVector' => $initializationVector,
        ];
        /** @var EncodedContext $encodedContext */
        $encodedContext = $this->objectManagerHelper->getObject(
            EncodedContext::class,
            array_filter($constructorArguments)
        );

        $this->assertSame($content, $encodedContext->getContent());
        $this->assertSame($initializationVector ?: '', $encodedContext->getInitializationVector());
    }

    /**
     * @return array
     */
    public function constructDataProvider()
    {
        return [
            'Without Initialization Vector' => ['content text', null],
            'With Initialization Vector' => ['content text', 'c51sd3c4sd68c5sd'],
        ];
    }
}
