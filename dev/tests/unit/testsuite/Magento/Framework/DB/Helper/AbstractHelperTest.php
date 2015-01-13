<?php
/**
 * \Magento\Framework\DB\Helper\AbstractHelper test case
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Helper;

class AbstractHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Helper\AbstractHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapterMock;

    protected function setUp()
    {
        $this->_adapterMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');

        $this->_resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->_resourceMock->expects($this->any())
            ->method('getConnection')
            ->with('prefix_read')
            ->will($this->returnValue($this->_adapterMock));

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Helper\AbstractHelper',
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
            ->will($this->returnArgument(0));

        $this->_model->expects($this->once())
            ->method('addLikeEscape')
            ->with($value, $options)
            ->will($this->returnArgument(0));

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
