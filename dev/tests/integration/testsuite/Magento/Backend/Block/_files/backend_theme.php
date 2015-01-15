<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var $registration \Magento\Core\Model\Theme\Registration */
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
    ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
    ->load(\Magento\Framework\App\Area::PART_CONFIG);
$registration = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Core\Model\Theme\Registration');
$registration->register('*/*/theme.xml');
