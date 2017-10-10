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
    public $externalVocab = null;
    public $strangeCounter3_9 = 0;
    public $totalParents = [];
    public $groupRules = [
        'av:Кол7_Таб2КодISIN',
        'av:Кол7_Таб8КодISIN',
        'av:Кол6_Таб3КодISIN',
        'av:Кол7_Таб34_2ОГРНДолжника',
        'av:Кол8_Таб1_1СуммаДенСред',
        'av:Кол3_Таб27ОГРНОбщ',
        'av:Кол3_Таб9ОГРНВекселедателя',
        'av:Кол6_Таб13КодISIN',
        'av:Кол2_Таб26_1НомерКредитДог',
        'av:Кол2_Таб26_2НомерКредитДог',
        'av:Кол8_Таб34_1ФактСуммаЗадолж',
        'av:Кол8_Таб34_2СтоимРасчетАктивов'
    ];

    public function getFullHeaders($parentNodePath, $xmlObject, $fullNodePath = '', $currentNodeName = '', $childNumber = 0, $groupUniqieValue = null) {

        if ($xmlObject->children()) {
            if ($fullNodePath !== '') {
                $this->fullHeaders[$fullNodePath]['nodeName'] = $currentNodeName;
                $this->fullHeaders[$fullNodePath][$this->fileNumber] = strval($xmlObject);
            }
            $childNumber = 0;
            if (is_null($groupUniqieValue)) {
                foreach ($xmlObject->children() as $childrenName => $childrenNode) {
                    if (in_array($childrenName, $this->groupRules)) {
                        $groupUniqieValue = strval($childrenNode);
                        $groupName = $childrenName;
                        if ($childrenName == 'av:Кол7_Таб34_2ОГРНДолжника') {
                            $uniqueier = 'av:Кол8_Таб34_2СуммаДенСредств';
                            $groupName .= ' и по ' . $uniqueier;
                            $groupUniqieValue .= $xmlObject->children()->$uniqueier;
                        } elseif ($childrenName == 'av:Кол6_Таб3КодISIN') {
                            $uniqueier = 'av:Кол3_Таб3ОГРН';
                            $groupName .= ' и по ' . $uniqueier;
                            $groupUniqieValue .= $xmlObject->children()->$uniqueier;
                        } elseif ($childrenName == 'av:Кол7_Таб8КодISIN') {
                            if (trim(str_replace('-', '', $groupUniqieValue)) == '') {
                                $groupUniqieValue=trim(str_replace('-', '', $groupUniqieValue));
                                $uniqueier        = 'av:Кол6_Таб8ГосРегНом';
                                $groupName        .= ' ОТМЕНЕНО, так как пусто!!!  и по ' . $uniqueier;
                                $groupUniqieValue .= $xmlObject->children()->$uniqueier;
                            }
                        } elseif ($childrenName == 'av:Кол3_Таб9ОГРНВекселедателя') {
                            $this->strangeCounter3_9++;
                            $groupUniqieValue .= '/' . $this->strangeCounter3_9;
                            $groupName .= ' и по номеру записи:' . $this->strangeCounter3_9;
                        }
                        $groupUniqieValue = str_replace('.', ',', $groupUniqieValue);
                        break;
                    }
                }
            }
            foreach ($xmlObject->children() as $nodeName => $node) {
                $childNumber++;
                if (in_array($nodeName, $this->totalParents)) {
                    $childNodePath = $parentNodePath . '/total/' . $nodeName;
                } else {
                    if (is_null($groupUniqieValue) || empty($groupUniqieValue)) {
                        $childNodePath = $fullNodePath . '/' . $nodeName . '/' . $childNumber;
                    } else {
                        if (strpos($fullNodePath, $groupUniqieValue) === false) {
                            $childNodePath = $parentNodePath . '/' . $groupUniqieValue . '/' . $nodeName;
                        } else {
                            $childNodePath = substr($fullNodePath, 0, strpos($fullNodePath, $groupUniqieValue)) . '/' . $groupUniqieValue . '/' . $nodeName;
                        }
                    }
                }
                if (isset($groupName)) {
                    $this->fullHeaders[$fullNodePath]['containsISINs'] = 'Группировка по ' . $groupName;
                }
                $this->getFullHeaders($fullNodePath, $node, $childNodePath, $nodeName, $childNumber, $groupUniqieValue);
            }
        } else {

            $this->fullHeaders[$fullNodePath]['parentNodePath'] = $parentNodePath;
            $this->fullHeaders[$fullNodePath]['nodeName'] = $currentNodeName;
            $this->fullHeaders[$fullNodePath]['fullNodePath'] = $fullNodePath;
            $this->fullHeaders[$fullNodePath][$this->fileNumber] = strval($xmlObject);
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

    public function getTotalParents($xmlObject, $parentNodeName = '') {
        if ($xmlObject->children()) {
            foreach ($xmlObject->children() as $nodeName => $node) {
                if (!isset($this->totalParents[$nodeName])) {
                    if (mb_strpos($nodeName, 'НомерГрафыИтого') !== false) {
                        $this->totalParents[$nodeName] = $nodeName;
                    }
                }
                $this->getTotalParents($node, $nodeName);
            }
        } else {
            return;
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

        $pattern = array('/,/', '/\\s{1,}/', '/«/', '/»/', '/"/');
        $replacement = array('.', '', '"', '"', '');
        $str1 = trim($str1);
        $str2 = trim($str2);
        $str1 = mb_strtolower($str1);
        $str2 = mb_strtolower($str2);
        $str1 = preg_replace($pattern, $replacement, $str1);
        $str2 = preg_replace($pattern, $replacement, $str2);

        if ($str1 == $str2) {
            return true;
        }
        $encodedCurrentVocab = $this->getExternalVocab();
        foreach ($encodedCurrentVocab as $ruleIndex => $rule) {
            if ($result == false) {
                $ruleSrt1 = $rule[0];
                $ruleSrt2 = $rule[1];
                $ruleSrt1 = mb_strtolower($ruleSrt1);
                $ruleSrt2 = mb_strtolower($ruleSrt2);
                $ruleSrt1 = preg_replace($pattern, $replacement, $ruleSrt1);
                $ruleSrt2 = preg_replace($pattern, $replacement, $ruleSrt2);

                if ($ruleSrt1 == $str1 && $str2 == $ruleSrt2) {
                    return true;
                }
                if ($ruleSrt2 == $str1 && $str2 == $ruleSrt1) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return $result;
    }

    function getExternalVocab() {
        if (!is_null($this->externalVocab)) {
            return $this->externalVocab;
        }
        $result = [];
        $currentVocabFileNames = glob('./vocab/*.csv');
        if (is_array($currentVocabFileNames) && count($currentVocabFileNames)) {
            $currentVocabFileName = array_pop($currentVocabFileNames);
            $result = $this->csv_to_array($currentVocabFileName, '~');
        }
//        echo 'Словарь подключен</br>';
        $this->externalVocab = $result;
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
