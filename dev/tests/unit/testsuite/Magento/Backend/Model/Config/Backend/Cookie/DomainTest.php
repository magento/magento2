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
namespace Magento\Backend\Model\Config\Backend\Cookie;

use Magento\Framework\Model\Exception;
use Magento\Framework\Session\Config\Validator\CookieDomainValidator;

/**
 * Test \Magento\Backend\Model\Config\Backend\Cookie\Domain
 */
class DomainTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Model\Resource\AbstractResource | \PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var \Magento\Backend\Model\Config\Backend\Cookie\Domain */
    protected $domain;

    /**
     * @var  CookieDomainValidator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    protected function setUp()
    {
        $eventDispatcherMock = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $contextMock = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $contextMock->expects(
            $this->any()
        )->method(
            'getEventDispatcher'
        )->will(
            $this->returnValue($eventDispatcherMock)
        );

        $this->resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\AbstractResource',
            [
                '_construct',
                '_getReadAdapter',
                '_getWriteAdapter',
                'getIdFieldName',
                'beginTransaction',
                'save',
                'commit',
                'addCommitCallback',
                'rollBack',
            ],
            [],
            '',
            false
        );

        $this->validatorMock = $this->getMockBuilder(
            'Magento\Framework\Session\Config\Validator\CookieDomainValidator'
        )->disableOriginalConstructor()
            ->getMock();
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->domain = $helper->getObject(
            'Magento\Backend\Model\Config\Backend\Cookie\Domain',
            [
                'context' => $contextMock,
                'resource' => $this->resourceMock,
                'configValidator' => $this->validatorMock,
            ]
        );
    }

    /**
     * @covers \Magento\Backend\Model\Config\Backend\Cookie\Domain::_beforeSave
     * @dataProvider beforeSaveDataProvider
     *
     * @param string $value
     * @param bool $isValid
     * @param int $callNum
     * @param int $callGetMessages
     */
    public function testBeforeSave($value, $isValid, $callNum, $callGetMessages = 0)
    {
        $this->resourceMock->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());
        $this->resourceMock->expects($this->any())->method('commit')->will($this->returnSelf());
        $this->resourceMock->expects($this->any())->method('rollBack')->will($this->returnSelf());

        $this->validatorMock->expects($this->exactly($callNum))
            ->method('isValid')
            ->will($this->returnValue($isValid));
        $this->validatorMock->expects($this->exactly($callGetMessages))
            ->method('getMessages')
            ->will($this->returnValue(['message']));
        $this->domain->setValue($value);
        try {
            $this->domain->save();
            if ($callGetMessages ) {
                $this->fail('Failed to throw exception');
            }
        } catch (Exception $e) {
            $this->assertEquals('Invalid domain name: message', $e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            'not string' => [['array'], false, 1, 1],
            'invalid hostname' => ['http://', false, 1, 1],
            'valid hostname' => ['hostname.com', true, 1, 0],
            'empty string' => ['', false, 0, 0],
        ];
    }
}
