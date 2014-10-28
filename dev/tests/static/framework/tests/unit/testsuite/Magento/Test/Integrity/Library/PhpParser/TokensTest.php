<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Library\PhpParser;

use Magento\TestFramework\Integrity\Library\PhpParser\Tokens;

/**
 */
class TokensTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tokens
     */
    protected $tokens;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\TestFramework\Integrity\Library\PhpParser\ParserFactory
     */
    protected $parseFactory;

    /**
     * Testable content
     *
     * @var string
     */
    protected $content = '<?php echo "test";';

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->parseFactory = $this->getMockBuilder(
            'Magento\TestFramework\Integrity\Library\PhpParser\ParserFactory'
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * Covered parse content
     *
     * @test
     */
    public function testParseContent()
    {
        $parser = $this->getMockBuilder(
            'Magento\TestFramework\Integrity\Library\PhpParser\Parser'
        )->getMockForAbstractClass();

        $this->parseFactory->expects($this->any())->method('createParsers')->will($this->returnValue(array($parser)));

        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->tokens->parseContent();
    }

    /**
     * Covered getDependencies method
     *
     * @test
     */
    public function testGetDependencies()
    {
        $uses = $this->getMockBuilder(
            'Magento\TestFramework\Integrity\Library\PhpParser\Uses'
        )->disableOriginalConstructor()->getMock();

        $this->parseFactory->expects($this->exactly(2))->method('getUses')->will($this->returnValue($uses));

        $staticCalls = $this->getMockBuilder(
            'Magento\TestFramework\Integrity\Library\PhpParser\StaticCalls'
        )->disableOriginalConstructor()->getMock();

        $staticCalls->expects(
            $this->once()
        )->method(
            'getDependencies'
        )->will(
            $this->returnValue(array('StaticDependency'))
        );

        $this->parseFactory->expects($this->once())->method('getStaticCalls')->will($this->returnValue($staticCalls));

        $throws = $this->getMockBuilder(
            'Magento\TestFramework\Integrity\Library\PhpParser\Throws'
        )->disableOriginalConstructor()->getMock();

        $throws->expects($this->once())->method('getDependencies')->will($this->returnValue(array('ThrowDependency')));

        $this->parseFactory->expects($this->once())->method('getThrows')->will($this->returnValue($throws));

        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->assertEquals(array('StaticDependency', 'ThrowDependency'), $this->tokens->getDependencies());
    }

    /**
     * Test code for get previous token from parameter "content"
     *
     * @test
     */
    public function testGetPreviousToken()
    {
        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->assertEquals(array(T_ECHO, 'echo', 1), $this->tokens->getPreviousToken(2));
    }

    /**
     * Covered getTokenCodeByKey
     *
     * @test
     */
    public function testGetTokenCodeByKey()
    {
        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->assertEquals(T_ECHO, $this->tokens->getTokenCodeByKey(1));
    }

    /**
     * Covered getTokenValueByKey
     *
     * @test
     */
    public function testGetTokenValueByKey()
    {
        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->assertEquals('echo', $this->tokens->getTokenValueByKey(1));
    }
}
