<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Behat\SoapExtension\Utils;

/**
 * Trait ArrayManager.
 *
 * @package Behat\SoapExtension\Utils
 */
trait ArrayManager
{
    /**
     * @param object $object
     *
     * @return array
     */
    public static function objectToArray($object)
    {
        return json_decode(json_encode($object), true);
    }

    /**
     * Retrieves a value from a nested array.
     *
     * @param array $array
     *   An array from which the value will be got.
     * @param array $parents
     *   An array of keys, starting with the outermost key.
     *
     * @return mixed
     */
    public static function arrayValue(array $array, array $parents)
    {
        foreach ($parents as $parent) {
            if (is_array($array) && array_key_exists($parent, $array)) {
                $array = $array[$parent];
            } else {
                return null;
            }
        }

        return $array;
    }
}
