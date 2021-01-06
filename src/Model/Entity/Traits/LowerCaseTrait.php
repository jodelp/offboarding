<?php
namespace App\Model\Entity\Traits;

trait LowerCaseTrait
{
    /**
     * _setUserName mutator for name field of Staff Entity
     * @param string $name
     * @return string
     */
    protected function _setUsername(string $name): string
    {
        return mb_convert_case($name, MB_CASE_LOWER);
    }

    /**
     * _setName mutator for name field of all Entity
     * @param string $name
     * @return string
     */
    protected function _setName(string $name): string
    {
        return mb_convert_case($name, MB_CASE_LOWER);
    }

    /**
     * _setStatus mutator for status field of all Entity
     * @param string $value
     * @return string
     */
    protected function _setStatus(string $value): string
    {
        return mb_convert_case($value, MB_CASE_LOWER);
    }
}