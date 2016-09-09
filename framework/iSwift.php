<?php

defined('ISWIFT_BEGIN_TIME') or define('ISWIFT_BEGIN_TIME', microtime(true));

defined('ISWIFT_PATH') or define('ISWIFT_PATH', __DIR__);
defined('APP_PATH') or define('APP_PATH', dirname(__DIR__));

defined('ISWIFT_DEBUG') or define('ISWIFT_DEBUG', true);

use iSwift\Base\UnknownClassException;

class iSwift
{
    public static $classMap = [];

    public static $app;

    public static function autoload($className)
    {
        if (isset(static::$classMap[$className])) {
            $classFile = static::$classMap[$className];
        } elseif (strpos($className, '\\') !== false) {
            $path = str_replace('\\', '/', $className) . '.php';
            $classFile = APP_PATH . '/' . $path;
            if (!is_file($classFile)) {
                $classFile = ISWIFT_PATH . '/' . $path;
                if (!is_file(ISWIFT_PATH . '/' . $path)) {
                    return;
                }
            }
            static::$classMap[$className] = $classFile;
        } else {
            return;
        }

        include($classFile);

        if (ISWIFT_DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new UnknownClassException("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }

    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }
}

spl_autoload_register(['iSwift', 'autoload'], true, true);
// iSwift::$classMap = [
//     'iSwift\\Application' => ISWIFT_PATH . '/Application.php',
//
// ];
