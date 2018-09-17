<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
                        'Magento\Framework\App\Response\Http' => 'Magento\Framework\App\Response\Http\Interceptor'
                    ]
            ]
        );
        $this->interceptedResponse = $this->_objectManager->create('\Magento\Framework\App\Response\Http');
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
        $this->assertTrue(is_subclass_of($header, 'Zend\Http\Header\HeaderInterface', false));
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
