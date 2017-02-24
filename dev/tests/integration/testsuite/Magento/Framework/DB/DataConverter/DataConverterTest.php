<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\DataConverter;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\Select\InQueryModifier;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\TestFramework\Helper\Bootstrap;

class DataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Framework/DB/DataConverter/_files/broken_admin_user.php
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessageRegExp
     * #Error converting field `extra` in table `admin_user` where `user_id`\=\d using.*#
     */
    public function testDataConvertErrorReporting()
    {
        /** @var \Magento\User\Model\User $user */
        $user = $this->objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername('broken_admin');
        $userId = $user->getId();

        /** @var Serialize $serializer */
        $serializer = $this->objectManager->create(Serialize::class);
        $serializedData = $serializer->serialize(['some' => 'data', 'other' => 'other data']);
        $serializedDataLength = strlen($serializedData);
        $brokenSerializedData = substr($serializedData, 0, $serializedDataLength - 6);
        /** @var AdapterInterface $adapter */
        $adapter = $user->getResource()->getConnection();
        $adapter->update(
            $user->getResource()->getTable('admin_user'),
            ['extra' => $brokenSerializedData],
            "user_id={$userId}"
        );

        /** @var InQueryModifier $queryModifier */
        $queryModifier = $this->objectManager->create(InQueryModifier::class, ['user_id' => $userId]);

        /** @var SerializedToJson $dataConverter */
        $dataConverter = $this->objectManager->get(SerializedToJson::class);

        /** @var FieldDataConverter $fieldDataConverter */
        $fieldDataConverter = $this->objectManager->create(
            FieldDataConverter::class,
            [
                'dataConverter' => $dataConverter
            ]
        );
        $fieldDataConverter->convert(
            $adapter,
            'admin_user',
            'user_id',
            'extra',
            $queryModifier
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Framework/DB/DataConverter/_files/broken_admin_user.php
     */
    public function testAlreadyConvertedDataSkipped()
    {
        /** @var \Magento\User\Model\User $user */
        $user = $this->objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername('broken_admin');
        $userId = $user->getId();

        /** @var Serialize $serializer */
        $serializer = $this->objectManager->create(Serialize::class);
        $serializedData = $serializer->serialize(['some' => 'data', 'other' => 'other data']);
        $serializedDataLength = strlen($serializedData);
        $brokenSerializedData = substr($serializedData, 0, $serializedDataLength - 6);
        /** @var AdapterInterface $adapter */
        $adapter = $user->getResource()->getConnection();
        $adapter->update(
            $user->getResource()->getTable('admin_user'),
            ['extra' => $brokenSerializedData],
            "user_id={$userId}"
        );

        $adapter->update(
            $user->getResource()->getTable('admin_user'),
            ['extra' => '[]'],
            "user_id={$userId}"
        );

        /** @var InQueryModifier $queryModifier */
        $queryModifier = $this->objectManager->create(InQueryModifier::class, ['user_id' => $userId]);

        /** @var SerializedToJson $dataConverter */
        $dataConverter = $this->objectManager->get(SerializedToJson::class);

        /** @var FieldDataConverter $fieldDataConverter */
        $fieldDataConverter = $this->objectManager->create(
            FieldDataConverter::class,
            [
                'dataConverter' => $dataConverter
            ]
        );
        $fieldDataConverter->convert(
            $adapter,
            'admin_user',
            'user_id',
            'extra',
            $queryModifier
        );

        $user->load($userId);
        $this->assertEquals([], $user->getExtra());
    }
}
