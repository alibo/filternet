<?php
namespace Filternet\Utility;

class Status
{
    /**
     * Get blocked status text
     *
     * @return string
     */
    public static function blocked()
    {
        return '<fg=red>Blocked</>';
    }

    /**
     * Get open status text
     *
     * @return string
     */
    public static function open()
    {
        return '<fg=green>Open</>';
    }

    /**
     * Get unknown status text
     *
     * @return string
     */
    public static function unknown()
    {
        return '<fg=blue>~UNKNOWN~</>';
    }
}