<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Search\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Search\Ui\Component\Listing\Column\SynonymActions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SynonymActionsTest extends TestCase
{
    /**
     * Stub synonym group id
     */
    private const STUB_SYNONYM_GROUP_ID = 1;

    /**
     * Synonym group delete url
     */
    private const SYNONYM_GROUP_DELETE_URL = 'http://localhost/magento2/admin/search/synonyms/delete/group_id/%d';

    /**
     * Synonym group edit url
     */
    private const SYNONYM_GROUP_EDIT_URL = 'http://localhost/magento2/admin/search/synonyms/edit/group_id/%d';

    /**
     * @var SynonymActions
     */
    private $synonymActions;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * Setup environment to test
     */
    protected function setup(): void
    {
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);

        $objectManager = new ObjectManager($this);

        $this->synonymActions = $objectManager->getObject(
            SynonymActions::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'data' => [
                    'name' => 'actions'
                ]
            ]
        );
    }

    /**
     * Test prepareDataSource() with data source has no item
     */
    public function testPrepareDataSourceWithNoItem()
    {
        $dataSource = [
            'data' => []
        ];
        $expected = [
            'data' => []
        ];
        /**
         * Assert Result
         */
        $this->assertEquals($expected, $this->synonymActions->prepareDataSource($dataSource));
    }

    /**
     * Test prepareDataSource() with data source has items
     */
    public function testPrepareDataSourceWithItems()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'group_id' => self::STUB_SYNONYM_GROUP_ID
                    ]
                ]
            ]
        ];

        $expected = [
            'data' => [
                'items' => [
                    [
                        'group_id' => self::STUB_SYNONYM_GROUP_ID,
                        'actions' => [
                            'delete' => [
                                'href' => sprintf(
                                    self::SYNONYM_GROUP_DELETE_URL,
                                    self::STUB_SYNONYM_GROUP_ID
                                ),
                                'label' => (string)__('Delete'),
                                'confirm' => [
                                    'title' => (string)__('Delete'),
                                    'message' => (string)__(
                                        'Are you sure you want to delete synonym group with id: %1?',
                                        self::STUB_SYNONYM_GROUP_ID
                                    )
                                ],
                                'post' => true
                            ],
                            'edit' => [
                                'href' => sprintf(
                                    self::SYNONYM_GROUP_EDIT_URL,
                                    self::STUB_SYNONYM_GROUP_ID
                                ),
                                'label' => (string)__('View/Edit'),
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->urlBuilderMock->method('getUrl')->willReturnMap(
            [
                [
                    SynonymActions::SYNONYM_URL_PATH_DELETE, ['group_id' => self::STUB_SYNONYM_GROUP_ID],
                    sprintf(self::SYNONYM_GROUP_DELETE_URL, self::STUB_SYNONYM_GROUP_ID)
                ],
                [
                    SynonymActions::SYNONYM_URL_PATH_EDIT, ['group_id' => self::STUB_SYNONYM_GROUP_ID],
                    sprintf(self::SYNONYM_GROUP_EDIT_URL, self::STUB_SYNONYM_GROUP_ID)
                ]
            ]
        );

        /**
         * Assert Result
         */
        $this->assertEquals($expected, $this->synonymActions->prepareDataSource($dataSource));
    }
}
