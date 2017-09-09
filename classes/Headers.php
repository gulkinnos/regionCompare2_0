<?php

/**
 * Description of Headers
 *
 * @author Aleksandr Golubev aka gulkinnos <gulkinnos@gmail.com>
 */
class Headers {

    public $fullHeaders = [];
    public $counter=0;
    
    public $fileNumber=0;


//put your code here
    public function getFullHeaders($parentNodeName, $xmlObject) {
        $this->counter++;
//        var_dump($this->counter);
//        var_dump($parentNodeName);
        if ($xmlObject->children()) {
            if ($parentNodeName !== '') {
                $this->fullHeaders[$parentNodeName][$this->fileNumber] = trim(strval($xmlObject));
            }
            foreach ($xmlObject->children() as $nodeName => $node) {
                $this->getFullHeaders($parentNodeName.'/'.$nodeName, $node);
            }
        } else {
            $this->fullHeaders[$parentNodeName][$this->fileNumber] = strval($xmlObject);
        }
    }

}
