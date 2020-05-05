<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cookie\Test\Unit\Model\Config\Backend;

use Magento\Cookie\Model\Config\Backend\Domain;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Session\Config\Validator\CookieDomainValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cookie\Model\Config\Backend\Domain
 */
class DomainTest extends TestCase
{
    /**
     * @var AbstractResource|MockObject
     */
    private $resourceMock;

    /**
     * @var Domain
     */
    private $domain;

    /**
     * @var CookieDomainValidator|MockObject
     */
    private $validatorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $eventDispatcherMock = $this->createMock(Manager::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($eventDispatcherMock);

        $this->resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(['getIdFieldName', 'save'])
            ->onlyMethods(['getConnection', 'beginTransaction', 'commit', 'addCommitCallback', 'rollBack'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validatorMock = $this->getMockBuilder(CookieDomainValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManagerHelper($this);
        $this->domain = $helper->getObject(
            Domain::class,
            [
                'context' => $contextMock,
                'resource' => $this->resourceMock,
                'configValidator' => $this->validatorMock,
            ]
        );
    }

    /**
     * @covers \Magento\Cookie\Model\Config\Backend\Domain::beforeSave
     * @dataProvider beforeSaveDataProvider
     *
     * @param string $value
     * @param bool $isValid
     * @param int $callNum
     * @param int $callGetMessages
     */
    public function testBeforeSave($value, $isValid, $callNum, $callGetMessages = 0): void
    {
        $this->resourceMock->expects($this->any())->method('addCommitCallback')->willReturnSelf();
        $this->resourceMock->expects($this->any())->method('commit')->willReturnSelf();
        $this->resourceMock->expects($this->any())->method('rollBack')->willReturnSelf();

        $this->validatorMock->expects($this->exactly($callNum))
            ->method('isValid')
            ->willReturn($isValid);
        $this->validatorMock->expects($this->exactly($callGetMessages))
            ->method('getMessages')
            ->willReturn(['message']);
        $this->domain->setValue($value);
        try {
            $this->domain->beforeSave();
            if ($callGetMessages) {
                $this->fail('Failed to throw exception');
            }
        } catch (LocalizedException $e) {
            $this->assertEquals('Invalid domain name: message', $e->getMessage());
        }
    }

    /**
     * Data Provider for testBeforeSave
     */
    public function beforeSaveDataProvider(): array
    {
        return [
            'not string' => [['array'], false, 1, 1],
            'invalid hostname' => ['http://', false, 1, 1],
            'valid hostname' => ['hostname.com', true, 1, 0],
            'empty string' => ['', false, 0, 0],
        ];
    }
}
