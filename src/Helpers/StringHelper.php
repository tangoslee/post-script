<?php

namespace Tangoslee\PostScript\Helpers;

class StringHelper
{
    /**
     * Compare two strings if the same or not
     *
     * @param string|numeric|null $string
     * @param string|numeric|null $other
     */
    public static function equals($string, $other, bool $caseSensitive = true): bool
    {
        if (is_array($other)) {
            $other = json_encode($other);
        }
        return $caseSensitive
            ? strcmp((string) $string, (string) $other) === 0
            : strcasecmp((string) $string, (string) $other) === 0;
    }

    /**
     * Transform a connected words with dash to Connected words
     *
     * @param bool $ucFirst // example, true: UpperCase, false: upperClass
     *
     * @example
     *  upper-case => UpperCase or upperCase
     *  UPPER_CASE => UpperCase or upperCase
     */
    public static function combineWords(?string $words = '', bool $ucFirst = true): string
    {
        $FS = '-';
        $text = str_replace($FS, '', ucwords(str_replace(['-', '_'], $FS, strtolower($words ?? '')), $FS));

        return $ucFirst ? $text : lcfirst($text);
    }

    /**
     * Transform a combined words to under base connected words
     */
    public static function extractWords(?string $words = ''): string
    {
        $string = $words ?? '';
        $string = preg_replace('/([a-z])([A-Z])/', "\\1_\\2", $string);
        $string = preg_replace('/([\d])([a-zA-Z])/', "\\1_\\2", $string);
        $string = preg_replace('/([a-zA-Z])([\d])/', "\\1_\\2", $string);

        return strtolower($string);
    }
}
