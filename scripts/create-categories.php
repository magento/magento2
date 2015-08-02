<?php
require dirname(__FILE__) . '/../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
require dirname(__FILE__) . '/abstract.php';

class CreateCategoriesApp extends AbstractApp
{

    public function launch()
    {
        $this->_objectManager->get('Magento\Framework\Registry')
            ->register('isSecureArea', true);

        for ($i=0; $i<200; ++$i) {
            $newCategoryName = 'Performance Category ' .$i;

            /** @var Magento\Catalog\Model\Category\Interceptor $newCategory */
            $newCategory = $this->_objectManager->create('\Magento\Catalog\Model\Category');
            $existingCategory = $newCategory->loadByAttribute('name', $newCategoryName);

            if (!$existingCategory) {
                $newCategory->setData(
                    array(
                        'name' => $newCategoryName,
                        'parent_id' => Magento\Catalog\Model\Category::TREE_ROOT_ID,
                        'attribute_set_id' => $newCategory->getDefaultAttributeSetId(),
                        'path' => Magento\Catalog\Model\Category::TREE_ROOT_ID,
                    )
                );
                $newCategory->save();

                echo "Created\t" . $newCategoryName . PHP_EOL;
            } else {
                $newCategory->delete();
                echo "Deleting\t" . $newCategoryName . PHP_EOL;
            }
        }

        return parent::launch();
    }
}

/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('CreateCategoriesApp');
$bootstrap->run($app);


