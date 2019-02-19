<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\CreditmemoCommentRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class CreditmemoCommentsListTest
 */
class CreditmemoCommentsListTest extends WebapiAbstract
{
    const SERVICE_NAME = 'salesCreditmemoManagementV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @magentoApiDataFixture Magento/Sales/_files/creditmemo_for_get.php
     */
    public function testCreditmemoCommentsList()
    {
        $comment = 'Credit Memo Comment';
        $objectManager = Bootstrap::getObjectManager();
        /** @var Collection $creditmemoCollection */
        $creditmemoCollection = $objectManager->get(Collection::class);

        /** @var CreditmemoInterface $creditmemo */
        $creditmemo = $creditmemoCollection->getFirstItem();
        $creditmemoComment = $objectManager->get(CreditmemoCommentInterfaceFactory::class)
            ->create(
                [
                    'data' => [
                        CreditmemoCommentInterface::COMMENT => $comment,
                        CreditmemoCommentInterface::PARENT_ID => $creditmemo->getEntityId(),
                        CreditmemoCommentInterface::IS_VISIBLE_ON_FRONT => true,
                        CreditmemoCommentInterface::IS_CUSTOMER_NOTIFIED => true,
                    ]
                ]
            );

        /** @var CreditmemoCommentRepositoryInterface $creditMemoRepository */
        $creditmemoRepository = $objectManager->get(CreditmemoCommentRepositoryInterface::class);
        $creditmemoRepository->save($creditmemoComment);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/creditmemo/' . $creditmemo->getEntityId() . '/comments',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getCommentsList',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, ['id' => $creditmemo->getEntityId()]);

        self::assertNotEmpty($result['items']);
        $item = $result['items'][0];
        self::assertNotEmpty($item[CreditmemoCommentInterface::ENTITY_ID]);
        self::assertEquals($comment, $item[CreditmemoCommentInterface::COMMENT]);
    }
}
