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
    protected function setup()
    {
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);

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
                        'group_id' => 1
                    ]
                ]
            ]
        ];
        $expected = [
            'data' => [
                'items' => [
                    [
                        'group_id' => 1,
                        'actions' => [
                            'delete' => [
                                'href' => 'http://localhost/magento2/admin/search/synonyms/delete/group_id/1',
                                'label' => (string)__('Delete'),
                                'confirm' => [
                                    'title' => (string)__('Delete'),
                                    'message' => (string)__(
                                        'Are you sure you want to delete synonym group with id: %1?',
                                        1
                                    )
                                ],
                                '__disableTmpl' => true
                            ],
                            'edit' => [
                                'href' => 'http://localhost/magento2/admin/search/synonyms/edit/group_id/1',
                                'label' => (string)__('View/Edit'),
                                '__disableTmpl' => true
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->urlBuilderMock->expects($this->at(0))->method('getUrl')
            ->with(SynonymActions::SYNONYM_URL_PATH_DELETE, ['group_id' => 1])
            ->willReturn('http://localhost/magento2/admin/search/synonyms/delete/group_id/1');

        $this->urlBuilderMock->expects($this->at(1))->method('getUrl')
            ->with(SynonymActions::SYNONYM_URL_PATH_EDIT, ['group_id' => 1])
            ->willReturn('http://localhost/magento2/admin/search/synonyms/edit/group_id/1');

        /**
         * Assert Result
         */
        $this->assertEquals($expected, $this->synonymActions->prepareDataSource($dataSource));
    }
}
