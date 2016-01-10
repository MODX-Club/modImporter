<?php

require_once dirname(__FILE__) . '/modimporterreader.class.php';

class modImporterXmlReader extends modImporterReader{
    
    /**
     * статусы обработки временных записей
     */
    # const PROCESSED_STATUS = 1;
    # const UNPROCESSED_STATUS = 0;
    #
    
    
    public function __construct(& $modx){
        
        parent::__construct($modx);
        
        $this->reader = new XMLReader();
        
        return;
    }
    
    
    protected function processProperties(){
        
        if(!$this->getProperty('filename')){
            return "Не был указан файл";
        }
        
        return parent::processProperties();
    }
    
    
#     public function read(array $provider, $callback = null){
#         
#         if(empty($provider['file'])){
#             return "Не был указан файл";
#         }
#         
#         if(empty($provider['schema'])){
#             return "Не была указана схема";
#         }
#         
#         # if(!$schema = $this->getJsonSchema('json_schema_price')){
#         #     return "Не была получена схема";
#         # }
#         
#         
#         $file = $provider['file'];
#         $schema = $provider['schema'];
#         
#         # print_r($schema);
#         
#         $reader = & $this->getReader($file);
#         #        
#         
#         /**/
#         while ($reader->read()){
#             
#             $node = $this->getNodeName($reader);            
#             
#             if(!$this->isNodeText($reader) && $this->getSchemaNodeByKey($schema, $node)){                                
# 
#                 if(isset($schema->parse) && $schema->parse){
#                     
#                     $xml = $this->getXMLNode($reader);                    
#                     
#                     # $result = $this->parseTMPPrice($reader, $schema);
#                     # if(count($result)){
#                     #     $this->_prices[] = $result;                                            
#                     # }  
#                     
#                     # print_r($xml);
#                     
#                     if(is_callable($callback)){
#                         $ok = $callback($xml);
#                         if($ok !== true){
#                             return $ok;
#                         }
#                     }
#                     
#                     $reader->next();                                 
#                 }                               
#                 
#             }            
#         }
#         
# 
#         # if($this->hasErrors()){
#         #     return false;
#         # }
#         # 
#         # $this->insertTMPPricesToDB();
#         # $this->_prices = array();
#         
#         return true;
#         
#     }
    
    public function read(array $provider, $callback = null){
        
        if(empty($provider['file'])){
            return "Не был указан файл";
        }
        
        # if(empty($provider['schema'])){
        #     return "Не была указана схема";
        # }
        
        # if(!$schema = $this->getJsonSchema('json_schema_price')){
        #     return "Не была получена схема";
        # }
        
        
        $file = $provider['file'];
        # $schema = $provider['schema'];
        
        # print_r($schema);
        
        if(!$reader = & $this->getReader()){
            
            return "Не был получен ридер";
        }
        
        
        if(!$reader->open($file)){
            
            return "Ошибка чтения источника";
        } 
        
        /**/
        while ($reader->read()){
            
            # $node = $this->getNodeName($reader);            
            
            # if(!$this->isNodeText($reader) && $this->getSchemaNodeByKey($schema, $node)){                                

                # if(isset($schema->parse) && $schema->parse){
                #     
                #     $xml = $this->getXMLNode($reader);                    
                    
                    # $result = $this->parseTMPPrice($reader, $schema);
                    # if(count($result)){
                    #     $this->_prices[] = $result;                                            
                    # }  
                    
                    # print_r($xml);
                    
                    if(is_callable($callback)){
                        # $ok = $callback($this, $node);
                        $ok = $callback($this);
                        if($ok !== true){
                            return $ok;
                        }
                    }
                    
                #     $reader->next();                                 
                # }                               
                
            # }            
        }
        

        # if($this->hasErrors()){
        #     return false;
        # }
        # 
        # $this->insertTMPPricesToDB();
        # $this->_prices = array();
        
        return true;
        
    }
    
    
    # protected $events = array();
    
    /**
     */
    # protected function & getReader($file) 
    # {
    #     $reader = new XMLReader();
    #     if(!is_file($file)){
    #         $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Can\'t load the file', '', __CLASS__);
    #         return false;
    #     }
    #     $reader->open($file);
    #     return $reader;
    # }
    public function & getReader() 
    {
        
        return $this->reader;
    }
    #
    
    /**
     * check the xml-node
     */
    public function isNode($nodeName, XMLReader $reader) 
    {
        return (($reader->nodeType == XMLReader::ELEMENT) && ($reader->name == $nodeName)) ? true : false;
    }
    #
    
    /**
     * check the text-xml-node
     */
    public function isNodeText(XMLReader $reader) 
    {
        return ($reader->nodeType == XMLReader::TEXT || $reader->nodeType == XMLReader::SIGNIFICANT_WHITESPACE) ? true : false;
    }
    #
    
    /**
     * check the end of xml-node
     */
    public function isNodeEnd($nodeName = null, XMLReader $reader) 
    {
        $cond = $reader->nodeType == XMLReader::END_ELEMENT;
        if(!$nodeName){
            return $cond;
        }
        # else        
        return ($cond && ($reader->name == $nodeName)) ? true : false;
    }
    #
    
    /**
     * get xml-node
     */
    public function getXMLNode($reader) 
    {
        return simplexml_load_string($reader->readOuterXML());
    }
    #
    
    /**
     *  get the node key 
     */
    public function getNodeName(XMLReader $reader){
        return $reader->name;
    }
    
    public function getSchemaNodeByKey(array & $schema, $nodeKey){              
        
        $keys = array_keys($schema);
        
        # print_r("\n". current($keys));
        
        if(count($keys) == 1 && $nodeKey == current($keys)){
            $schema = $schema[$nodeKey];            
        }
        
        return $schema;
    }
    
    
    public static function toArray( $xml ) {
        if(is_string( $xml )){
            $xml = new SimpleXMLElement( $xml );
        }
        
        if(!$xml OR !($xml instanceof SimpleXMLElement)){
            return false;
        }
        
        $children = $xml->children();
        if ( !$children ) return (string) $xml;
        $arr = array();
        foreach ( $children as $key => $node ) {
            $node = modImporterXmlReader::toArray( $node );

            // support for 'anon' non-associative arrays
            if ( $key == 'anon' ) $key = count( $arr );

            // if the node is already set, put it into an array
            if ( isset( $arr[$key] ) ) {
                if ( !is_array( $arr[$key] ) || $arr[$key][0] == null ) $arr[$key] = array( $arr[$key] );
                $arr[$key][] = $node;
            } else {
                $arr[$key] = $node;
            }
        }
        return $arr;
    }
    
    
    # protected function getJsonSchema($schema_name){
    #     return json_decode('{
    #         "КоммерческаяИнформация": {
    #             "parse": true
    #             ,"ПакетПредложений": {}
    #         }
    #     }');
    #     
    #     # return json_decode('{
    #     #   "КоммерческаяИнформация": {
    #     #     "ПакетПредложений": {
    #     #       "Предложения": {
    #     #         "Предложение": {
    #     #           "parse": "true",
    #     #           "Ид":{
    #     #             "type":"string",
    #     #             "field": "good_id"
    #     #           },
    #     #           "Артикул":{
    #     #             "type":"string",
    #     #             "field":"article"
    #     #           },
    #     #           "Наименование":{
    #     #             "type":"string"
    #     #           },
    #     #           "Штрихкод":{
    #     #             "type":"string"
    #     #           },
    #     #           "Цены":{
    #     #             "Цена":{
    #     #               "validate":{
    #     #                 "key": "ЦенаЗаЕдиницу",
    #     #                 "cond": "gt:0"
    #     #               },
    #     #               "ИдТипаЦены":{
    #     #                 "type":"string",
    #     #                 "field":"type_id"
    #     #               },
    #     #               "ЦенаЗаЕдиницу":{
    #     #                 "type":"float",
    #     #                 "field":"value"
    #     #               },
    #     #               "Валюта":{
    #     #                 "type":"string",
    #     #                 "field":"currency_name"
    #     #               },
    #     #               "Единица":{
    #     #                 "type":"string"
    #     #               },
    #     #               "Коэффициент":{
    #     #                 "type":"integer"
    #     #               }
    #     #             }
    #     #           }
    #     #         }
    #     #       }
    #     #     }
    #     #   }
    #     # }');
    # }
    
    
    
    /**
     * insert rows to the DB
     */
#     protected function insertInDataBase($table, array $rows, array $columns) 
#     {
#         $columns_str = implode(", ", $columns);
#         $sql = "INSERT INTO {$table} 
#             ({$columns_str}) 
#             VALUES \n";
#         $sql.= implode(",\n", $rows) . ";";
#         $s = $this->modx->prepare($sql);
#         
#         if($this->getProperty('debug')){
#             $this->modx->log(xPDO::LOG_LEVEL_DEBUG, print_r($sql,1), '', __CLASS__);
#         }
# 
#         $result = $s->execute();
#         if (!$result) 
#         {
#             $this->modx->log(xPDO::LOG_LEVEL_WARN, 'SQL ERROR Import');
#             $this->modx->log(xPDO::LOG_LEVEL_WARN, print_r($s->errorInfo() , 1));
#             $this->modx->log(xPDO::LOG_LEVEL_WARN, $sql);
#         }
#         return $result;
#     }
    #
    
    /**
     * log items left
     */
    # protected function logCount($class, xPDOQuery & $q, $name = 'items') 
    # {
    #     $c = clone $q;
    #     $c->limit(0);
    #     $count = $this->modx->getCount($class, $c);
    #     
    #     if($this->getProperty('debug')){
    #         $this->modx->log(xPDO::LOG_LEVEL_DEBUG, "{$count} {$name} left…");        
    #     }
    # }
    #
    
    /**
     */
    # public function process() 
    # {
    #     return true;
    # }
    #
    
        
    // Находим ID документа по артикулу
    # protected function getResourceIdByArticle($article) 
    # {
    #     $result = null;
    #     $article_tv_id = $this->modx->getOption('shopmodx1c.article_tv_id');
    #     if ($article) 
    #     {
    #         $q = $this->modx->newQuery('modTemplateVarResource', array(
    #             "tmplvarid" => $article_tv_id,
    #             "value" => $article,
    #         ));
    #         $q->select(array(
    #             'contentid',
    #         ));
    #         $q->limit(1);
    #         $result = $this->modx->getValue($q->prepare());
    #     }
    #     return $result;
    # }
    
}