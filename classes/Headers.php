<?php

/**
 * Description of Headers
 *
 * @author Aleksandr Golubev aka gulkinnos <gulkinnos@gmail.com>
 */
class Headers {

    public $fullHeaders = [];
    public $counter = 0;
    public $fileNumber = 0;
    public $externalVocab=null;

    public function getFullHeaders($parentNodeName, $xmlObject, $currentNodeName = '') {
        $this->counter++;
        if ($xmlObject->children()) {
            if ($parentNodeName !== '') {
                $this->fullHeaders[$parentNodeName]['nodeName'] = $currentNodeName;
                $this->fullHeaders[$parentNodeName][$this->fileNumber] = strval($xmlObject);
            }
            foreach ($xmlObject->children() as $nodeName => $node) {
                $this->getFullHeaders($parentNodeName . '/' . $nodeName, $node, $nodeName);
            }
        } else {
            $this->fullHeaders[$parentNodeName]['nodeName'] = $currentNodeName;
            $this->fullHeaders[$parentNodeName][$this->fileNumber] = strval($xmlObject);
        }
    }

    public function compareValues() {
        foreach ($this->fullHeaders as $nodeXpath => $values) {
            $this->fullHeaders[$nodeXpath]['difference'] = 'different';
            if (isset($values[1]) && isset($values[2])) {
                if ($this->srtringsIdentical($values[1], $values[2])) {
                    $this->fullHeaders[$nodeXpath]['difference'] = 'identical';
                }
            }
        }
    }
/**
 * 
 * @param type $str1
 * @param type $str2
 * @return boolean
 */
     function srtringsIdentical($str1, $str2) {
         /**
          * @todo Подумать и ускорить
          */
        $result = false;
        if ($str1 == $str2) {
            return true;
        }
        $encodedCurrentVocab = $this->getExternalVocab();
        foreach ($encodedCurrentVocab as $ruleIndex => $rule) {
            if ($result == false) {
                $pattern = array('/,/', '/\\s{1,}/', '/«/', '/»/', '/"/');
                $replacement = array('.', '', '"', '"', '');
                $str1 = trim($str1);
                $str2 = trim($str2);
                $str1 = mb_strtolower($str1);
                $str2 = mb_strtolower($str2);
                $str1 = preg_replace($pattern, $replacement, $str1);
                $str2 = preg_replace($pattern, $replacement, $str2);

                $ruleSrt1 = $rule[0];
                $ruleSrt2 = $rule[1];
                $ruleSrt1 = mb_strtolower($ruleSrt1);
                $ruleSrt2 = mb_strtolower($ruleSrt2);
                $ruleSrt1 = preg_replace($pattern, $replacement, $ruleSrt1);
                $ruleSrt2 = preg_replace($pattern, $replacement, $ruleSrt2);

                if ($ruleSrt1 == $str1) {
                    if ($str2 == $ruleSrt2) {
                        return true;
                    }
                }
                if ($result == false) {
                    if ($ruleSrt2 == $str1) {
                        if ($str2 == $ruleSrt1) {
                            return true;
                        }
                    }
                }
                if ($result == false) {
                    if ($ruleSrt1 == $str2) {
                        if ($str1 == $ruleSrt2) {
                            return true;
                        }
                    }
                    if ($result == FALSE) {
                        if ($ruleSrt2 == $str2) {
                            if ($str1 == $ruleSrt1) {
                                return true;
                            }
                        }
                    }
                }
            } else {
                return true;
            }
        }

        return $result;
    }
    
    function srtringsIdenticalOldGavno($str1, $str2) {
        $result = false;
        $encodedCurrentVocab = $this->getExternalVocab();
                echo $str1.'<>'.$str2.'<hr>';

        foreach ($encodedCurrentVocab as $ruleIndex => $rule) {
            if ($result == FALSE) {
                $pattern = array('/,/', '/\\s{1,}/', '/«/', '/»/', '/"/');
                $replacement = array('.', '', '"', '"', '');
                $str1 = trim($str1);
                $str2 = trim($str2);
                $str1 = mb_strtolower($str1);
                $str2 = mb_strtolower($str2);
                $str1 = preg_replace($pattern, $replacement, $str1);
                $str2 = preg_replace($pattern, $replacement, $str2);

                $ruleSrt1 = $rule[0];
                $ruleSrt2 = $rule[1];
                $ruleSrt1 = mb_strtolower($ruleSrt1);
                $ruleSrt2 = mb_strtolower($ruleSrt2);
                $ruleSrt1 = preg_replace($pattern, $replacement, $ruleSrt1);
                $ruleSrt2 = preg_replace($pattern, $replacement, $ruleSrt2);

                if ($ruleSrt1 == $str1) {
                    if ($str2 == $ruleSrt2) {
                        $result = true;
                    }
                }
                if ($result == FALSE) {
                    if ($ruleSrt2 == $str1) {
                        if ($str2 == $ruleSrt1) {
                            $result = true;
                        }
                    }
                }
                if ($result == FALSE) {
                    if ($ruleSrt1 == $str2) {
                        if ($str1 == $ruleSrt2) {
                            $result = true;
                        }
                    }
                    if ($result == FALSE) {
                        if ($ruleSrt2 == $str2) {
                            if ($str1 == $ruleSrt1) {
                                $result = true;
                            }
                        }
                    }
                }
            }
        }
        if ($str1 == $str2) {
            $result = true;
        }
        return $result;
    }

    function getExternalVocab() {
        if(!is_null($this->externalVocab)){
            return $this->externalVocab;
        }
        $result = [];
        $currentVocabFileNames = glob('./vocab/*.csv');
        if (is_array($currentVocabFileNames) && count($currentVocabFileNames)) {
            $currentVocabFileName = array_pop($currentVocabFileNames);
            $result = $this->csv_to_array($currentVocabFileName, '~');
        }
        echo 'Словарь подключен</br>';
        $this->externalVocab=$result;
        return $result;
    }

    function csv_to_array($filename = '', $delimiter = ',') {
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = $row;
                }
            }
            fclose($handle);
        }
        return $data;
    }

}
