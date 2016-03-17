<?php
class modImporterGetActionsProcessor extends modObjectProcessor {

    public function process(){
        
        $namespace = 'modimporter';
        $core_path = $this->modx->getObject('modNamespace', $namespace)->getCorePath();
        
        $processors_path = $core_path . 'processors/modimporter/';
        
        //$dirs = new RecursiveDirectoryIterator($processors_path, RecursiveDirectoryIterator::SKIP_DOTS);
        
        
        $results = array();
        $total = 0;
        
        $rdi = new recursiveDirectoryIterator($processors_path, RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new recursiveIteratorIterator($rdi);
        while( $it->valid())
        {
            $dir = $it->getSubPathName();
            if(strpos($dir, '/console.class.php')){
                $processor = str_replace('console.class.php', 'console', $dir);
                $name = str_replace('/console.class.php', "", $dir);
                $results[] = array("name" => $name, "action" => $processor);
                $total++;
            }
            $it->next();
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