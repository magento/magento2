<?php
/**
 * \Magento\Framework\DB\Helper\AbstractHelper test case
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Helper;

class AbstractHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Helper\AbstractHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_adapterMock;

    protected function setUp(): void
    {
        $this->_adapterMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);

        $this->_resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_resourceMock->expects($this->any())
            ->method('getConnection')
            ->with('prefix')
            ->willReturn($this->_adapterMock);

        $this->_model = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Helper\AbstractHelper::class,
            [$this->_resourceMock, 'prefix'],
            '',
            true,
            true,
            true,
            ['addLikeEscape']
        );
    }

    /**
     * @param string $expected
     * @param array $data
     * @dataProvider escapeLikeValueDataProvider
     */
    public function testEscapeLikeValue($expected, array $data)
    {
        $this->assertEquals($expected, $this->_model->escapeLikeValue($data['value'], $data['options']));
    }

    public function testGetCILike()
    {
        $field = 'field';
        $value = 'value';
        $options = [];

        $this->_adapterMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with($field)
            ->willReturnArgument(0);

        $this->_model->expects($this->once())
            ->method('addLikeEscape')
            ->with($value, $options)
            ->willReturnArgument(0);

        $result = $this->_model->getCILike($field, $value, $options);
        $this->assertInstanceOf('Zend_Db_Expr', $result);
        $this->assertEquals($field . ' LIKE ' . $value, (string)$result);
    }

    /**
     * @return array
     */
    public function escapeLikeValueDataProvider()
    {
        return [
            [
                '',
                [
                    'value' => '',
                    'options' => []
                ],
            ],
            [
                'LIKE \%string\_end',
                [
                    'value' => 'LIKE %string_end',
                    'options' => []
                ]
            ],
            [
                'LIKE \%string_end',
                [
                    'value' => 'LIKE %string_end',
                    'options' => [
                        'allow_symbol_mask' => true,
                    ]
                ]
            ],
            [
                'LIKE %string\_end',
                [
                    'value' => 'LIKE %string_end',
                    'options' => [
                        'allow_string_mask' => true,
                    ]
                ]
            ],
            [
                'LIKE %string_end',
                [
                    'value' => 'LIKE %string_end',
                    'options' => [
                        'allow_symbol_mask' => true,
                        'allow_string_mask' => true,
                    ]
                ]
            ],
            [
                '%string%',
                [
                    'value' => 'string',
                    'options' => [
                        'position' => 'any',
                    ]
                ]
            ],
            [
                'string%',
                [
                    'value' => 'string',
                    'options' => [
                        'position' => 'start',
                    ]
                ]
            ],
            [
                '%string',
                [
                    'value' => 'string',
                    'options' => [
                        'position' => 'end',
                    ]
                ]
            ]
        ];
    }
}
