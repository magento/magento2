<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Ui\DataProvider\Grouper;

/**
 * Class MetaTest
 */
class GrouperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Grouper
     */
    protected $grouper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->grouper = $this->objectManager->getObject(Grouper::class);
    }

    public function testGroupMetaElementMinimalOptions()
    {
        $meta = [
            'group1' => [
                'children' => [
                    'element1' => [
                        'label' => 'Element 1',
                        'sortOrder' => 10
                    ],
                    'element2' => [
                        'label' => 'Element 2',
                        'sortOrder' => 20
                    ]
                ]
            ]
        ];

        $elements = ['element1', 'element2'];

        $result = [
            'group1' => [
                'children' => [
                    'element1' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'dataScope' => '',
                        'label' => 'Element 1',
                        'sortOrder' => 10,
                        'children' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'sortOrder' => 10,
                                'dataScope' => 'element1'
                            ],
                            'element2' => [
                                'label' => 'Element 2',
                                'sortOrder' => 20,
                                'dataScope' => 'element2'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertSame($result, $this->grouper->groupMetaElements($meta, $elements));
    }

    public function testGroupMetaElementsDifferentGroups()
    {
        $meta = [
            'group1' => [
                'children' => [
                    'element1' => [
                        'label' => 'Element 1',
                        'sortOrder' => 10
                    ]
                ]
            ],
            'group2' => [
                'children' => [
                    'element2' => [
                        'label' => 'Element 2',
                        'sortOrder' => 10
                    ]
                ]
            ]
        ];

        $elements = [
            'element1' => [
                'requiredMeta' => [
                    'required' => true
                ]
            ],
            'element2' => [
                'meta' => [
                    'additionalClasses' => 'inline'
                ]
            ]
        ];

        $result = [
            'group1' => [
                'children' => [
                    'element1' => [
                        'label' => 'Element 1',
                        'sortOrder' => 10,
                        'required' => true
                    ]
                ]
            ],
            'group2' => [
                'children' => [
                    'element2' => [
                        'label' => 'Element 2',
                        'sortOrder' => 10
                    ]
                ]
            ]
        ];

        $this->assertSame($result, $this->grouper->groupMetaElements($meta, $elements));
    }

    public function testGroupMetaElementsFullOptions()
    {
        $meta = [
            'group1' => [
                'children' => [
                    'element1' => [
                        'label' => 'Element 1',
                        'sortOrder' => 10
                    ],
                ]
            ],
            'group2' => [
                'children' => [
                    'element2' => [
                        'label' => 'Element 2',
                        'sortOrder' => 10
                    ]
                ]
            ]
        ];

        $elements = [
            'element1' => [
                'requiredMeta' => [
                    'required' => true
                ],
                'meta' => [
                    'additionalClasses' => 'inline'
                ]
            ],
            'element2' => [
                'isTarget' => true,
                'requiredMeta' => [
                    'validation' => [
                        'validate-number' => true
                    ]
                ],
                'meta' => [
                    'additionalClasses' => 'inline last',
                    'sortOrder' => 20
                ]
            ]
        ];

        $groupOptions = [
            'targetCode' => 'container1',
            'groupNonSiblings' => true,
            'meta' => [
                'additionalClasses' => 'group'
            ]
        ];

        $result = [
            'group1' => [
                'children' => []
            ],
            'group2' => [
                'children' => [
                    'container1' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'dataScope' => '',
                        'label' => 'Element 2',
                        'sortOrder' => 10,
                        'additionalClasses' => 'group',
                        'children' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'sortOrder' => 10,
                                'required' => true,
                                'additionalClasses' => 'inline',
                                'dataScope' => 'element1'
                            ],
                            'element2' => [
                                'label' => 'Element 2',
                                'sortOrder' => 20,
                                'validation' => [
                                    'validate-number' => true
                                ],
                                'additionalClasses' => 'inline last',
                                'dataScope' => 'element2'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertSame($result, $this->grouper->groupMetaElements($meta, $elements, $groupOptions));
    }
}
