<?php

declare(strict_types=1);

namespace Magento\Contact\Setup\Patch\Data;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as ResourceModel;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory as ResourceFactory;
use Magento\UrlRewrite\Model\UrlRewrite;

/**
 * Class UpdateUrlRewriteForContactController
 */
class UpdateUrlRewriteForContactController implements DataPatchInterface
{

    /** @var ModuleDataSetupInterface $setup */
    private $setup;

    /** @var UrlRewriteCollectionFactory $urlRewriteCollectionFactory */
    private $urlRewriteCollectionFactory;

    /** @var ResourceFactory $urlRewriteResourceModel */
    private $urlRewriteResourceModelFactory;

    /**
     * UpdateUrlRewriteForContactController constructor.
     *
     * @param ModuleDataSetupInterface $setup
     * @param UrlRewriteCollectionFactory $urlRewriteCollectionFactory
     * @param ResourceFactory $urlRewriteResourceModelFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory,
        ResourceFactory $urlRewriteResourceModelFactory
    ) {
        $this->setup = $setup;
        $this->urlRewriteCollectionFactory = $urlRewriteCollectionFactory;
        $this->urlRewriteResourceModelFactory = $urlRewriteResourceModelFactory;
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

        try {
            /** @var UrlRewrite $urlRewrite */
            $urlRewrite = $this->getContactUsUrlRewriteModel();

            $urlRewrite->setTargetPath(ConfigInterface::CONTACT_US_ROUTE_INDEX_PATH);
            $this->save($urlRewrite);

        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not update url for contact path: "%1"', $e->getMessage()), $e);
        }

        $this->setup->endSetup();
    }

    /**
     * @return bool|AbstractModel|UrlRewrite
     */
    private function getContactUsUrlRewriteModel()
    {
        /** @var UrlRewriteCollection $urlRewriteCollection */
        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();

        $urlRewriteCollection->addFieldToFilter('request_path', ['eq' => 'contact']);

        return $urlRewriteCollection->fetchItem();
    }

    /**
     * @param UrlRewrite $urlRewrite
     *
     * @throws AlreadyExistsException
     */
    private function save(UrlRewrite $urlRewrite): void
    {
        /** @var ResourceModel $urlRewriteResourceModel */
        $urlRewriteResourceModel = $this->urlRewriteResourceModelFactory->create();

        $urlRewriteResourceModel->save($urlRewrite);
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [InstallDefaultContactUsCmsPage::class];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
