<?php
class modImporterGetActionsProcessor extends modObjectProcessor {

    public function process(){
        
        $namespace = 'modimporter';
        $core_path = $this->modx->getObject('modNamespace', $namespace)->getCorePath();
        
        $processors_path = $core_path . 'processors/modimporter/';
        
        //$dirs = new RecursiveDirectoryIterator($processors_path, RecursiveDirectoryIterator::SKIP_DOTS);

        $exclude = array('export');
        $filter = function ($fileInfo, $key, $iterator) use ($exclude) {
            return $fileInfo->isFile() || !in_array($fileInfo->getBaseName(), $exclude);
        };
        
        $results = array();
        $total = 0;
        
        $rdi = new RecursiveDirectoryIterator($processors_path, RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator($rdi, $filter)
        );

        foreach ($it as $pathname => $fileInfo) {
            $dir = str_replace($processors_path, "", $pathname);
            if(strpos($dir, '/console.class.php')){
                $processor = str_replace('console.class.php', 'console', $dir);
                $name = str_replace('/console.class.php', "", $dir);
                $results[] = array("name" => $name, "type" => $processor);
                $total++;
            }
        }
        
        $ret = array(
            "success" => true,
            "total" => $total,
            "results" => $results
        );
        return json_encode($ret);
    }

}

return 'modImporterGetActionsProcessor';