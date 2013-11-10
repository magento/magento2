<?php
class DuplicatedLocaleChecker {
	protected $rows = array();

	public function getAppCodeDir() {
		return __DIR__ . '/..';
	}

	public function getTargetDir() {
		return __DIR__ ;
	}

	public function saveCSV($namespace , $lang_code = 'en_US') {
		if(!$this->matches) return false;
		$rows = array();
		foreach($this->matches AS $key=>$value){
			$key = str_replace('\\' , '' , $key);
			$value = __($key);
			if($key == $value) $rows[] = array($key , $value);
		}
		$fp = fopen($this->getTargetDir() . '/' . $namespace . '/' . $lang_code . '.csv', 'w');
		foreach ($rows as $fields) {
		    fputcsv($fp, $fields);
		}

		fclose($fp);
		return true;
	}

	public function getCSV($file) {
		$rows = array();
		if (($handle = fopen($file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		    	$row = array();
		        $num = count($data);
		        if($num>=1) {
					$rows[$data[0]] = $data[1];
		        }
		    }
		    fclose($handle);
		}
		return $rows;
	}

	public function collect() {
		$code_dir = $this->getAppCodeDir();
		$namespaces = array();
		$module_base_dirs = glob($code_dir.'/*');
		foreach($module_base_dirs AS $module_base_dir) {
			$namespace_prefix = array_pop(explode('/' , $module_base_dir));
			if($namespace_prefix <> 'Magento' ) continue;
			$module_dirs = glob($module_base_dir.'/*');
			foreach($module_dirs AS $module_dir) {
				$namespace = $namespace_prefix . '_' . array_pop(explode('/' , $module_dir));
				$this->iterate($module_dir , $namespace);
				// $this->saveCSV($namespace , 'en_US');
			}
			$dup_rows = array();
			foreach ($this->rows as $key => $row ){
				if($row['count']>2) {
					$dup_rows[$key] = $row; 
				}
			}
			error_log(print_r($dup_rows,true));
			error_log(count($dup_rows));
		}
	}

	public function process($file , $namespace , $level) {
		$rows = $this->getCSV($file);
		foreach ($rows as $key => $value) {
			$values = array();
			$count = 1;
			if(isset($this->rows[$key])) {
				$count  = $this->rows[$key]['count']+1;
				$values = $this->rows[$key]['values'];
			}
			$values[$namespace] = $value;
			$this->rows[$key] = array(
				'values' => $values,
				'count' => $count,
			);
		}
	}

	public function iterate($path , $namespace , $level = 0) {
		if(is_file($path) && preg_match('/zh_CN\.csv/' , $path)) {
			$this->process($path , $namespace , $level);
		} else {
			$sub_dirs = glob($path.'/*');
			$level++;
			foreach ($sub_dirs as $sub_dir) {
				$this->iterate($sub_dir , $namespace , $level);
			}
		}
	}
}

$checker = new DuplicatedLocaleChecker();
$checker->collect();