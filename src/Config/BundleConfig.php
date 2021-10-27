<?php
/*******************************************************************
 * (c) 2021 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\UtilsBundle\Config;


use Contao\Model;
use Contao\System;


class BundleConfig
{
    static string $namespace    = 'IIDO';
    static string $subNamespace = 'UtilsBundle';

    static string $bundleName   = "utils-bundle";
    static string $bundleGroup  = "2do";



    /**
     * @TODO custom return order?!
     */
    public static function getBundleConfigArray( bool $includeListener = true ): array
    {
        return array(self::getNamespace(), self::getSubNamespace(), self::getSubName(), self::getPrefix(), self::getTablePrefix( true ), self::getListenerName( $includeListener ));
    }



    /**
     * Get Bundle Config Data from function name
     *
     * @param string       $funcName  Name of the get function
     * @param null|string  $funcVar   function variable
     *
     * @return string
     */
    public static function getBundleConfig( string $funcName, ?string $funcVar = null ): string
    {
        $functionName = 'get' . $funcName;

        if( function_exists($functionName) )
        {
            return self::$functionName();
        }
        else
        {
            $funcName = preg_replace('/_/', '', $funcName);
            switch( $funcName )
            {
                case "namespace":
                    $return = self::getNamespace();
                    break;

                case "subnamespace":
                    $return = self::getSubNamespace();
                    break;

                case "subname":
                    $return = self::getSubName();
                    break;

                case "prefix":
                    $return = self::getPrefix();
                    break;

                case "table":
                case "tableprefix":
                    $return = self::getTablePrefix();
                    break;

                case "field":
                case "fieldprefix":
                case "tablefield":
                case "tablefieldprefix":
                    $return = self::getTableFieldPrefix();
                    break;

                case "listener":
                case "listenername":
                    $funcVar    = (($funcVar === null) ? false : $funcVar);
                    $return     = self::getListenerName( $funcVar );
                    break;

                case "bundle":
                case "bundlename":
                    $return = self::getBundleName();
                    break;

                case "group":
                case "groupName":
                    $return = self::getBundleGroup();
                    break;

                case "path":
                case "bundlepath":
                    $return = self::getBundlePath();
                    break;

                case "bundlepathpublic":
                case "publicbundlepath":
                case "bundlePathPublic":
                case "publicBundlePath":
                    $funcVar = (($funcVar === NULL) ? FALSE : $funcVar);
                    $return  = self::getBundlePath(TRUE, $funcVar);
                    break;

                default:
                    $return = "Variable / Funktion existiert nicht!";
            }

            return $return;
        }

    }



    public static function getNamespace(): string
    {
        return static::$namespace;
    }



    public static function getSubNamespace(): string
    {
        return static::$subNamespace;
    }



    public static function getSubName(): string
    {
        return strtolower( preg_replace('/Bundle$/', '', static::$subNamespace) );
    }



    public static function getPrefix(): string
    {
        return strtolower(static::$namespace);
    }



    public static function getTablePrefix( bool $includeSubname = true ): string
    {
        return 'tl_' . self::getPrefix() . '_' . ( $includeSubname ? self::getSubName() . '_' : '');
    }



    public static function getTableFieldPrefix(): string
    {
        return strtolower( self::getNamespace() ) . ucfirst( self::getSubName() ) . '_';
    }



    public static function getListenerName( bool $includeListener = false ): string
    {
        return self::getPrefix() . '_' . self::getSubName() . ($includeListener ? '.listener' : '');
    }



    public static function getBundleName(): string
    {
        return static::$bundleName;
    }



    public static function getBundleGroup(): string
    {
        return static::$bundleGroup;
    }



    public static function getBundlePath( bool $public = false, bool $includeWebFolder = true): string
    {
        if( $public )
        {
            return ($includeWebFolder ? 'web/' : '') . 'bundles/' . self::getPrefix() . self::getSubName();
        }
        else
        {
            $rootDir    = self::getRootDir();
            $bundleName = self::getBundleGroup() . '/' . self::getBundleName();
            $addon      = '';

//            if( is_dir($rootDir . '/vendor/' . $bundleName . '/src') )
//            {
//                $addon = '/src';
//            }

            return 'vendor/' . $bundleName . $addon;
        }
    }



    public static function getFileTable( string $fileName ): string
    {
        return self::getTableName( $fileName);
    }



    public static function getTableName( string $fileName ): string
    {
        $arrParts       = explode("/", $fileName);
        $arrFileParts   = explode(".", array_pop( $arrParts ));

        array_pop( $arrFileParts );

        $fileName       = implode(".", $arrFileParts);

        return $fileName;
    }



    public static function getTableClass( string $strTable ): string
    {
        $classParts    = explode("\\", Model::getClassFromTable($strTable));
        $tableClass    = preg_replace(array('/^Iido/', '/Model$/'), '', array_pop($classParts));
        $arrClass      = preg_split('/(?=[A-Z])/', lcfirst($tableClass));
        $iidoTable     = (bool) preg_match('/^tl_iido/', $strTable);
        $newTableClass = (($iidoTable) ? 'IIDO\\' : 'IIDO\\' . self::getSubNamespace() . '\\Table\\');

        foreach( $arrClass as $i => $class)
        {
            $newTableClass .= ucfirst($class);

            if( $i === 0 )
            {
                if( $iidoTable )
                {
                    $newTableClass .= 'Bundle\\Table\\';
                }
            }

            if( $i === (count($arrClass) - 1) )
            {
                $newTableClass .= 'Table';
            }
        }

        return $newTableClass;
    }



    public static function getContaoVersion(): string
    {
        $packages = System::getContainer()->getParameter('kernel.packages');
        return $packages['contao/core-bundle'];
    }



    public static function isActiveBundle( string $bundleName ): bool
    {
        $packages = System::getContainer()->getParameter('kernel.packages');
        return key_exists($bundleName, $packages);
    }



    public static function getRootDir( bool $includeSlash = false ): string
    {
//        return dirname(System::getContainer()->getParameter('kernel.project_dir')) . ($includeSlash ? '/' : '');
        return System::getContainer()->getParameter('kernel.project_dir') . ($includeSlash ? '/' : '');
    }
}
