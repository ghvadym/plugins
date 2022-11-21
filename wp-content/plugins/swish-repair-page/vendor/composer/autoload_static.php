<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitec956837a62b4b555a883b62651283fe
{
    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/../..' . '/app',
        1 => __DIR__ . '/../..' . '/app/API',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->fallbackDirsPsr4 = ComposerStaticInitec956837a62b4b555a883b62651283fe::$fallbackDirsPsr4;
            $loader->classMap = ComposerStaticInitec956837a62b4b555a883b62651283fe::$classMap;

        }, null, ClassLoader::class);
    }
}
