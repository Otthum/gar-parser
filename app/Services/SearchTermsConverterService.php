<?php

namespace App\Services;

class SearchTermsConverterService
{
    public static function convert(string $string)
    {
        $string = preg_replace('~, *~', ' ', $string);
        
        $terms = explode(' ', $string);

        $result = '';
        foreach ($terms as $key => $term) {

            if (trim($term) == '') {
                continue;
            }

            $result .= self::convertWordToShort($term);
            if (array_key_last($terms) != $key) {
                $result .= " ";
            }
            
        }

        /* $result .= '*'; */
        return $result;
    }

    protected static function convertWordToShort(string $word)
    {
        $map = [
            /* 'а.обл.' => 'автономная область',
            'АО' => 'автономная область',
            'Аобл' => 'автономная область',
            'б-р' => 'бульвар',
            'вн.р-н' => 'внутригородской район',
            'вн р-н' => 'внутригородской район',
            'вн.тер.г' => 'внутригородская территория',
            'г' => 'город',
            'г.' => 'город',
            'г-к' => 'городок',
            'г.о.' => 'городской округ',
            'г.п.' => 'городское поселение',
            'гп' => 'городской посёлок',
            'обл' => 'область',
            'респ' => 'республика', */

            'б р' => [
                'бульвар',
                'бульва',
                'бульв',
                'буль',
                'бул',
                'бу',
                'б-р'
            ],

            'ул' => [
                "улица",
                "улиц",
                "ули"
            ],
        ];

        foreach ($map as $short => $fulls) {
            foreach ($fulls as $full) {
                if ($full == $word) {
                    $shortParts = explode(' ', $short);
                    $result = '';
                    foreach ($shortParts as $part) {
                        $result .= '+' . $part . ' ';
                    }
                    return trim($result);
                }
            }
        }

        /* if (in_array(strtolower($word), $map)) {
            return '+' . array_search($word, $map) . '*';
        } */

        return "+" . $word . "*";
    }
}