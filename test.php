<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$om = $bootstrap->getObjectManager();

$state = $om->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$pc = $om->get(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
    ->setPageSize(10)
    ->load();
var_dump($pc);

//
///** @var \Magento\Eav\Model\Cache\Type $cache */
//$cache = $om->get(\Magento\Eav\Model\Cache\Type::class);
//$i = 0;
//$d = 0;
//while (true) {
//    echo '-\\|/'[$i % 4], "\r";
//    $key = "my_test_data" . $i++;
//    $cache->save('data',$key , ['tag1', 'tag2' ], 1000);
//    if ( 'data' !== $cache->load($key)) {
//        $d ++;
//       echo ' : Missread is - ' . number_format($d/$i, 2) , "\r";
//    }
//}