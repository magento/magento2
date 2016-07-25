<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Unit\Controller\Adminhtml\Product\Attribute\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Validate;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject;

class ValidateTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider providerData
     * @param array $dataRequest
     * @param boolean $isError
     */
    public function testAfterExecute(array $dataRequest, $isError)
    {
        $errorsCount = ($isError) ? 1 : 0;
        /** @var PHPUnit_Framework_MockObject_MockObject|RequestInterface $request */
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPostValue'])
            ->getMockForAbstractClass();
        $request->expects($this->once())->method('getPostValue')->willReturn($dataRequest);

        /** @var Validate | PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(Validate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subject->expects($this->any())
            ->method("getRequest")
            ->willReturn($request);

        /** @var Json | PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Swatches\Controller\Adminhtml\Product\Attribute\Plugin\Validate $controller */
        $controller = (new ObjectManager($this))
            ->getObject('\Magento\Swatches\Controller\Adminhtml\Product\Attribute\Plugin\Validate');

        $response->expects($this->exactly($errorsCount))
            ->method('setJsonData');
        $controller->afterExecute($subject, $response);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function providerData()
    {
        return [
            [
                [

                ], false
            ],
            [
                [
                    "frontend_input" => "test"
                ], false
            ],
            [
                [
                    "frontend_input" => "select",
                    "option" => [
                        'value' => [],
                        'delete' => []
                    ]
                ], false
            ],
            [
                [
                    "frontend_input" => "select",
                    "option" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [2, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ]
                    ]
                ], false
            ],
            [
                [
                    "frontend_input" => "select",
                    "option" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [1, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ]
                    ]
                ], true
            ],
            [
                [
                    "frontend_input" => "select",
                    "option" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [1, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "1",
                            "option_2" => "",
                        ]
                    ]
                ], false
            ],
            [
                [
                    "frontend_input" => "swatch_text",
                    "optiontext" => [
                        'value' => [],
                        'delete' => []
                    ]
                ], false
            ],
            [
                [
                    "frontend_input" => "swatch_text",
                    "optiontext" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [2, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ]
                    ]
                ], false
            ],
            [
                [
                    "frontend_input" => "swatch_text",
                    "optiontext" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [1, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ]
                    ]
                ], true
            ],
            [
                [
                    "frontend_input" => "swatch_text",
                    "optiontext" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [1, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "1",
                            "option_2" => "",
                        ]
                    ]
                ], false
            ],
            [
                [
                    "frontend_input" => "swatch_visual",
                    "optionvisual" => [
                        'value' => [],
                        'delete' => []
                    ]
                ], false
            ],
            [
                [
                    "frontend_input" => "swatch_visual",
                    "optionvisual" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [2, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ]
                    ]
                ], false
            ],
            [
                [
                    "frontend_input" => "swatch_visual",
                    "optionvisual" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [1, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ]
                    ]
                ], true
            ],
            [
                [
                    "frontend_input" => "swatch_visual",
                    "optionvisual" => [
                        'value' => [
                            "option_0" => [1, 0],
                            "option_1" => [1, 0],
                            "option_2" => [3, 0],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "1",
                            "option_2" => "",
                        ]
                    ]
                ], false
            ],
        ];
    }
}
