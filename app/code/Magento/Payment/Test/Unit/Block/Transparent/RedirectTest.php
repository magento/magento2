<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Block\Transparent;

use Magento\Payment\Block\Transparent\Redirect;
use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $url;
    /**
     * @var Redirect
     */
    private $model;
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->context->method('getRequest')
            ->willReturn($this->request);
        $this->url = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->model = new Redirect(
            $this->context,
            $this->url
        );
    }

    /**
     * @param array $postData
     * @param array $expected
     * @dataProvider getPostParamsDataProvider
     */
    public function testGetPostParams(array $postData, array $expected): void
    {
        $this->request->method('getPostValue')
            ->willReturn($postData);
        $this->assertEquals($expected, $this->model->getPostParams());
    }

    /**
     * @return array
     */
    public function getPostParamsDataProvider(): array
    {
        return [
            [
                [
                    'BILLTOEMAIL' => 'john.doe@magento.lo',
                    'BILLTOSTREET' => '3640 Holdrege Ave',
                    'BILLTOZIP' => '90016',
                    'BILLTOLASTNAME' => 'Ãtienne',
                    'BILLTOFIRSTNAME' => 'Ãillin',
                ],
                [
                    'BILLTOEMAIL' => 'john.doe@magento.lo',
                    'BILLTOSTREET' => '3640 Holdrege Ave',
                    'BILLTOZIP' => '90016',
                    'BILLTOLASTNAME' => 'Ãtienne',
                    'BILLTOFIRSTNAME' => 'Ãillin',
                ]
            ],
            [
                [
                    'BILLTOEMAIL' => 'john.doe@magento.lo',
                    'BILLTOSTREET' => '3640 Holdrege Ave',
                    'BILLTOZIP' => '90016',
                    'BILLTOLASTNAME' => mb_convert_encoding('Ãtienne', 'ISO-8859-1'),
                    'BILLTOFIRSTNAME' => mb_convert_encoding('Ãillin', 'ISO-8859-1'),
                ],
                [
                    'BILLTOEMAIL' => 'john.doe@magento.lo',
                    'BILLTOSTREET' => '3640 Holdrege Ave',
                    'BILLTOZIP' => '90016',
                    'BILLTOLASTNAME' => 'Ãtienne',
                    'BILLTOFIRSTNAME' => 'Ãillin',
                ]
            ]
        ];
    }
}
