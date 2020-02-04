<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreStart
namespace Magento\PageCache\Model\App\Response {
    $mockPHPFunctions = false;

    function headers_sent()
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions) {
            return false;
        }

        return call_user_func_array('\headers_sent', func_get_args());
    }
}

namespace Magento\PageCache\Test\Unit\Model\App\Response {

    use Magento\Framework\App\Response\Http;
    use Magento\MediaStorage\Model\File\Storage\Response;
    use Magento\PageCache\Model\App\Response\HttpPlugin;

    // @codingStandardsIgnoreEnd

    class HttpPluginTest extends \PHPUnit\Framework\TestCase
    {
        /**
         * @inheritdoc
         */
        protected function setUp()
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = true;
        }

        /**
         * @inheritdoc
         */
        protected function tearDown()
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = false;
        }

        /**
         * @param string $responseInstanceClass
         * @param int $sendVaryCalled
         * @return void
         *
         * @dataProvider beforeSendResponseDataProvider
         */
        public function testBeforeSendResponse(string $responseInstanceClass, int $sendVaryCalled): void
        {
            /** @var Http | \PHPUnit_Framework_MockObject_MockObject $responseMock */
            $responseMock = $this->createMock($responseInstanceClass);
            $responseMock->expects($this->exactly($sendVaryCalled))
                ->method('sendVary');
            $plugin = new HttpPlugin();
            $plugin->beforeSendResponse($responseMock);
        }

        /**
         * @return array
         */
        public function beforeSendResponseDataProvider(): array
        {
            return [
                [Http::class, 1],
                [Response::class, 0]
            ];
        }
    }
}
