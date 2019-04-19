<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Fixture for generating coupon codes
 *
 * Support the following format:
 * <!-- Number of coupon codes -->
 * <coupon_codes>{int}</coupon_codes>
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 */
class CouponCodesFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 129;

    /**
     * @var int
     */
    protected $couponCodesCount = 0;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    private $couponCodeFactory;

    /**
     * Constructor
     *
     * @param FixtureModel $fixtureModel
     * @param \Magento\SalesRule\Model\RuleFactory|null $ruleFactory
     * @param \Magento\SalesRule\Model\CouponFactory|null $couponCodeFactory
     */
    public function __construct(
        FixtureModel $fixtureModel,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory = null,
        \Magento\SalesRule\Model\CouponFactory $couponCodeFactory = null
    ) {
        parent::__construct($fixtureModel);
        $this->ruleFactory = $ruleFactory ?: $this->fixtureModel->getObjectManager()
            ->get(\Magento\SalesRule\Model\RuleFactory::class);
        $this->couponCodeFactory = $couponCodeFactory ?: $this->fixtureModel->getObjectManager()
            ->get(\Magento\SalesRule\Model\CouponFactory::class);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $this->fixtureModel->resetObjectManager();
        $this->couponCodesCount = $this->fixtureModel->getValue('coupon_codes', 0);
        if (!$this->couponCodesCount) {
            return;
        }

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->create(\Magento\Store\Model\StoreManager::class);
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->get(\Magento\Catalog\Model\Category::class);

        //Get all websites
        $categoriesArray = [];
        $websites = $storeManager->getWebsites();
        foreach ($websites as $website) {
            //Get all groups
            $websiteGroups = $website->getGroups();
            foreach ($websiteGroups as $websiteGroup) {
                $websiteGroupRootCategory = $websiteGroup->getRootCategoryId();
                $category->load($websiteGroupRootCategory);
                $categoryResource = $category->getResource();
                //Get all categories
                $resultsCategories = $categoryResource->getAllChildren($category);
                foreach ($resultsCategories as $resultsCategory) {
                    $category->load($resultsCategory);
                    $structure = explode('/', $category->getPath());
                    if (count($structure) > 2) {
                        $categoriesArray[] = [$category->getId(), $website->getId()];
                    }
                }
            }
        }
        asort($categoriesArray);
        $categoriesArray = array_values($categoriesArray);

        $this->generateCouponCodes($this->ruleFactory, $this->couponCodeFactory, $categoriesArray);
    }

    /**
     * Generate Coupon Codes
     *
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\CouponFactory $couponCodeFactory
     * @param array $categoriesArray
     *
     * @return void
     */
    public function generateCouponCodes($ruleFactory, $couponCodeFactory, $categoriesArray)
    {
        for ($i = 0; $i < $this->couponCodesCount; $i++) {
            $ruleName = sprintf('Coupon Code %1$d', $i);
            $data = [
                'rule_id'               => null,
                'name'                  => $ruleName,
                'is_active'             => '1',
                'website_ids'           => $categoriesArray[$i % count($categoriesArray)][1],
                'customer_group_ids'    => [
                    0 => '0',
                    1 => '1',
                    2 => '2',
                    3 => '3',
                ],
                'coupon_type'           => \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC,
                'conditions'            => [],
                'simple_action'         => \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION,
                'discount_amount'       => 5,
                'discount_step'         => 0,
                'stop_rules_processing' => 1,
            ];

            $model = $ruleFactory->create();
            $model->loadPost($data);
            $useAutoGeneration = (int)!empty($data['use_auto_generation']);
            $model->setUseAutoGeneration($useAutoGeneration);
            $model->save();

            $coupon = $couponCodeFactory->create();
            $coupon->setRuleId($model->getId())
                ->setCode('CouponCode' . $i)
                ->setIsPrimary(true)
                ->setType(0);
            $coupon->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function getActionTitle()
    {
        return 'Generating coupon codes';
    }

    /**
     * @inheritdoc
     */
    public function introduceParamLabels()
    {
        return [
            'coupon_codes' => 'Coupon Codes'
        ];
    }
}
