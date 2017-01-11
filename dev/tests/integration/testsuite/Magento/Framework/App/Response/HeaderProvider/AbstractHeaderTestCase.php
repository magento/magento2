<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

abstract class AbstractHeaderTestCase extends \Magento\TestFramework\TestCase\AbstractController
{
    /** @var  \Magento\Framework\App\Response\Http */
    private $interceptedResponse;

    public function setUp()
    {
        parent::setUp();
        $this->_objectManager->configure(
            [
                'preferences' =>
                    [
                        \Magento\Framework\App\Response\Http::class =>
                            \Magento\Framework\App\Response\Http\Interceptor::class
                    ]
            ]
        );
        $this->interceptedResponse = $this->_objectManager->create(\Magento\Framework\App\Response\Http::class);
    }

    /**
     * Verify that a given header matches a given value
     *
     * @param string $name
     * @param string $value
     */
    protected function assertHeaderPresent($name, $value)
    {
        $this->interceptedResponse->sendResponse();

        $header = $this->interceptedResponse->getHeader($name);
        $this->assertTrue(is_subclass_of($header, \Zend\Http\Header\HeaderInterface::class, false));
        $this->assertSame(
            $value,
            $header->getFieldValue()
        );
    }

    protected function assertHeaderNotPresent($name)
    {
        $this->interceptedResponse->sendResponse();
        $this->assertFalse($this->interceptedResponse->getHeader($name));
    }
}
