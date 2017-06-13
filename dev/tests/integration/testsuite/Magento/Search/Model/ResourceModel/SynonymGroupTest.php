<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\ResourceModel;

class SynonymGroupTest extends \PHPUnit_Framework_TestCase
{
    public function testGetByScope()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel1 */
        $synonymGroupModel1 = $objectManager->create(\Magento\Search\Model\SynonymGroup::class);
        $synonymGroupModel1->setWebsiteId(0);
        $synonymGroupModel1->setStoreId(0);
        $synonymGroupModel1->setSynonymGroup('a,b,c');
        $synonymGroupModel1->save();
        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel2 */
        $synonymGroupModel2 = $objectManager->create(\Magento\Search\Model\SynonymGroup::class);
        $synonymGroupModel2->setWebsiteId(0);
        $synonymGroupModel2->setStoreId(1);
        $synonymGroupModel2->setSynonymGroup('d,e,f');
        $synonymGroupModel2->save();
        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel3 */
        $synonymGroupModel3 = $objectManager->create(\Magento\Search\Model\SynonymGroup::class);
        $synonymGroupModel3->setWebsiteId(1);
        $synonymGroupModel3->setStoreId(0);
        $synonymGroupModel3->setSynonymGroup('g,h,i');
        $synonymGroupModel3->save();
        /** @var \Magento\Search\Model\SynonymGroup $synonymGroupModel4 */
        $synonymGroupModel4 = $objectManager->create(\Magento\Search\Model\SynonymGroup::class);
        $synonymGroupModel4->setWebsiteId(0);
        $synonymGroupModel4->setStoreId(0);
        $synonymGroupModel4->setSynonymGroup('d,e,f');
        $synonymGroupModel4->save();

        /** @var \Magento\Search\Model\ResourceModel\SynonymGroup $resourceModel */
        $resourceModel = $objectManager->create(\Magento\Search\Model\ResourceModel\SynonymGroup::class);
        $this->assertEquals(
            [['group_id' => 1, 'synonyms' => 'a,b,c'], ['group_id' => 4, 'synonyms' => 'd,e,f']],
            $resourceModel->getByScope(0, 0)
        );
        $this->assertEquals([['group_id' => 2, 'synonyms' => 'd,e,f']], $resourceModel->getByScope(0, 1));
        $this->assertEquals([['group_id' => 3, 'synonyms' => 'g,h,i']], $resourceModel->getByScope(1, 0));
    }
}
