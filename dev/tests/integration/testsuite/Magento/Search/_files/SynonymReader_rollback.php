<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $synonymsModel \Magento\Search\Model\ResourceModel\SynonymReader */
$synonymsModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Search\Model\ResourceModel\SynonymReader'
);
$synonymsModel->getConnection()->truncateTable('search_synonyms');