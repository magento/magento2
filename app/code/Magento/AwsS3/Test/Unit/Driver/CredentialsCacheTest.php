<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Unit\Driver;

use Aws\Credentials\CredentialsFactory;
use Magento\AwsS3\Driver\CredentialsCache;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CredentialsCacheTest extends TestCase
{
    /**
     * @var CredentialsCache
     */
    private $adapter;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var CredentialsFactory|MockObject
     */
    private $credentialsFactory;

    /**
     * @var Json|MockObject
     */
    private $jsonMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->credentialsFactory =
            $this->getMockBuilder(CredentialsFactory::class)->disableOriginalConstructor()->getMock();
        $this->jsonMock = $this->createMock(Json::class);
        $this->adapter = new CredentialsCache($this->cacheMock, $this->credentialsFactory, $this->jsonMock);
    }

    public function testSet()
    {
        $this->jsonMock->expects($this->once())->method('serialize')->with('value')->willReturn('serialized');
        $this->cacheMock->expects($this->once())->method('save')->with('serialized', 'key');
        $this->adapter->set('key', 'value');
    }

    public function testGetEmpty()
    {
        $this->cacheMock->expects($this->once())->method('load')->with('key');
        $actual = $this->adapter->get('key');
        $this->assertEquals(null, $actual);
    }

    public function testRemove()
    {
        $this->cacheMock->expects($this->once())->method('remove');
        $this->adapter->remove('key');
    }
}
