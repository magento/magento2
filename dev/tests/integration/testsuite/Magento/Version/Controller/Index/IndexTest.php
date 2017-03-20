<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Version\Controller\Index;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testIndexAction()
    {
        // Execute controller to get version response
        $this->dispatch('magento_version/index/index');
        $body = $this->getResponse()->getBody();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\ProductMetadataInterface $productMetadata */
        $productMetadata = $objectManager->get(\Magento\Framework\App\ProductMetadataInterface::class);
        $name = $productMetadata->getName();
        $edition = $productMetadata->getEdition();

        $fullVersion = $productMetadata->getVersion();
        $versionParts = explode('.', $fullVersion);
        $majorMinor = $versionParts[0] . '.' . $versionParts[1];

        // Response must contain Major.Minor version, product name, and edition
        $this->assertContains($majorMinor, $body);
        $this->assertContains($name, $body);
        $this->assertContains($edition, $body);

        // Response must not contain full version including patch version
        $this->assertNotContains($fullVersion, $body);
    }
}
