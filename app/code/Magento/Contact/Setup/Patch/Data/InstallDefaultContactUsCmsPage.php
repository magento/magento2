<?php

declare(strict_types=1);

namespace Magento\Contact\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class InstallDefaultContactUsCmsPage
 */
class InstallDefaultContactUsCmsPage implements DataPatchInterface
{

    /** @var ModuleDataSetupInterface $setup */
    private $setup;

    /** @var PageRepositoryInterfaceFactory $pageRepository */
    private $pageRepositoryFactory;

    /** @var PageInterfaceFactory $pageInterfaceFactory */
    private $pageInterfaceFactory;

    /** @var string */
    private const CMS_CONTACT_US_LAYOUT = <<<EOD
<referenceContainer name="content">
    <block class="Magento\Contact\Block\ContactForm" name="contactForm" template="Magento_Contact::form.phtml">
        <container name="form.additional.info" label="Form Additional Info"/>
        <arguments>
            <argument name="view_model" xsi:type="object">Magento\Contact\ViewModel\UserDataProvider</argument>
        </arguments>
    </block>
</referenceContainer>
EOD;

    /** @var string index for stores scope config */
    private const CONTACT_US_STORES = 'stores';

    /**
     * InstallDefaultContactUsCmsPage constructor.
     *
     * @param ModuleDataSetupInterface $setup
     * @param PageRepositoryInterfaceFactory $pageRepositoryInterfaceFactory
     * @param PageInterfaceFactory $pageInterfaceFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        PageRepositoryInterfaceFactory $pageRepositoryInterfaceFactory,
        PageInterfaceFactory $pageInterfaceFactory
    ) {
        $this->setup = $setup;
        $this->pageRepositoryFactory = $pageRepositoryInterfaceFactory;
        $this->pageInterfaceFactory = $pageInterfaceFactory;
    }

    /**
     * Installs cms contact us page
     *
     * @return DataPatchInterface|void
     * @throws CouldNotSaveException
     */
    public function apply()
    {
        $this->setup->startSetup();

        $contactUsPage = [
            PageInterface::TITLE => 'Contact Us',
            PageInterface::PAGE_LAYOUT => '1column',
            PageInterface::IDENTIFIER => 'contact',
            PageInterface::CONTENT_HEADING => 'Contact Us',
            PageInterface::LAYOUT_UPDATE_XML => self::CMS_CONTACT_US_LAYOUT,
            PageInterface::IS_ACTIVE => true,
            PageInterface::SORT_ORDER => 0,
            self::CONTACT_US_STORES => [0]
        ];

        try {
            /** @var PageInterface $cmsPageModel */
            $cmsPageModel = $this->pageInterfaceFactory->create();
            $cmsPageModel->setData($contactUsPage);

            /** @var PageRepositoryInterface $cmsPageRepository */
            $cmsPageRepository = $this->pageRepositoryFactory->create();
            $cmsPageRepository->save($cmsPageModel);

        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save CMS Contact Us page: "%1"', $e->getMessage()), $e);
        }

        $this->setup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
