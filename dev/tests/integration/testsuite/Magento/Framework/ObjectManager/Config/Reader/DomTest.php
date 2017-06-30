<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Config\Reader;

/**
 * Class DomTest @covers \Magento\Framework\ObjectManager\Config\Reader\Dom
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_fileList;

    /**
     * @var \Magento\Framework\App\Arguments\FileResolver\Primary
     */
    protected $_fileResolverMock;

    /**
     * @var \DOMDocument
     */
    protected $_mergedConfig;

    /**
     * @var \Magento\Framework\App\Arguments\ValidationState
     */
    protected $_validationState;

    /**
     * @var \Magento\Framework\ObjectManager\Config\SchemaLocator
     */
    protected $_schemaLocator;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Mapper\Dom
     */
    protected $_mapper;

    protected function setUp()
    {
        $fixturePath = realpath(__DIR__ . '/../../_files') . '/';
        $this->_fileList = [
            file_get_contents($fixturePath . 'config_one.xml'),
            file_get_contents($fixturePath . 'config_two.xml'),
        ];

        $this->_fileResolverMock = $this->getMock(
            \Magento\Framework\App\Arguments\FileResolver\Primary::class,
            [],
            [],
            '',
            false
        );
        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue($this->_fileList));
        $this->_mapper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\ObjectManager\Config\Mapper\Dom::class,
            ['argumentInterpreter' => $this->getArgumentInterpreterWithMockedStringUtils()]
        );
        $this->_validationState = new \Magento\Framework\App\Arguments\ValidationState(
            \Magento\Framework\App\State::MODE_DEFAULT
        );
        $this->_schemaLocator = new \Magento\Framework\ObjectManager\Config\SchemaLocator();

        $this->_mergedConfig = new \DOMDocument();
        $this->_mergedConfig->load($fixturePath . 'config_merged.xml');
    }

    public function testRead()
    {
        $model = new \Magento\Framework\ObjectManager\Config\Reader\Dom(
            $this->_fileResolverMock,
            $this->_mapper,
            $this->_schemaLocator,
            $this->_validationState
        );
        $this->assertEquals($this->_mapper->convert($this->_mergedConfig), $model->read('scope'));
    }

    /**
     * Replace Magento\Framework\Data\Argument\Interpreter\StringUtils with mock to check arguments wasn't translated.
     *
     * Check argument $data has not key $data['translate'], therefore
     * Magento\Framework\Data\Argument\Interpreter\StringUtils::evaluate($data) won't translate $data['value'].
     *
     * @return \Magento\Framework\Data\Argument\Interpreter\Composite
     */
    private function getArgumentInterpreterWithMockedStringUtils()
    {
        $booleanUtils = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Stdlib\BooleanUtils::class
        );
        $stringUtilsMock = $this->getMockBuilder(\Magento\Framework\Data\Argument\Interpreter\StringUtils::class)
            ->setConstructorArgs(['booleanUtils' => $booleanUtils])
            ->setMethods(['evaluate'])
            ->getMock();
        $stringUtilsMock->expects($this->any())
            ->method('evaluate')
            ->with(self::callback(function ($data) {
                return !isset($data['translate']);
            }))
            ->will(self::returnCallback(function ($data) {
                return isset($data['value']) ? $data['value'] : '';
            }));
        $constInterpreter = new \Magento\Framework\Data\Argument\Interpreter\Constant();
        $composite = new \Magento\Framework\Data\Argument\Interpreter\Composite(
            [
                'boolean' => new \Magento\Framework\Data\Argument\Interpreter\Boolean($booleanUtils),
                'string' => $stringUtilsMock,
                'number' => new \Magento\Framework\Data\Argument\Interpreter\Number(),
                'null' => new \Magento\Framework\Data\Argument\Interpreter\NullType(),
                'object' => new \Magento\Framework\Data\Argument\Interpreter\DataObject($booleanUtils),
                'const' => $constInterpreter,
                'init_parameter' => new \Magento\Framework\App\Arguments\ArgumentInterpreter($constInterpreter),
            ],
            \Magento\Framework\ObjectManager\Config\Reader\Dom::TYPE_ATTRIBUTE
        );
        $composite->addInterpreter('array', new \Magento\Framework\Data\Argument\Interpreter\ArrayType($composite));

        return $composite;
    }
}
