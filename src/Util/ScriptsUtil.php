<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\UtilsBundle\Util;


use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Controller;
use Contao\Folder;
use Contao\StringUtil;
use IIDO\UtilsBundle\Config\BundleConfig;
use IIDO\UtilsBundle\Util\BasicUtil;
use IIDO\UtilsBundle\Util\PageUtil;


/**
 * Class Script Helper
 * @package IIDO\Configundle
 */
class ScriptsUtil
{
    const PATH          = 'Resources/public/%s/';
    const FILES_PATH    = 'files/%s/%s/';
    const BUNDLE_PATH   = 'bundles/%s/%s/';
    const VENDOR_PATH   = 'vendor/2do/%s-bundle/' . self::PATH;

    const FOLDER_NAME   = 'scripts';

    const MODE          = '|static';


    protected PageUtil $pageUtil;
    protected BasicUtil $basicUtil;

    protected string $bundleName = '';
    protected string $bundlePath = '';
    protected string $vendorPath = '';



    public function __construct( BasicUtil $basicUtil, PageUtil $pageUtil )
    {
        $this->basicUtil = $basicUtil;
        $this->pageUtil = $pageUtil;
    }



    public function setBundleName( string $bundleName ): void
    {
        $this->bundlePath = \sprintf(self::BUNDLE_PATH, 'iido' . $bundleName, self::FOLDER_NAME);
        $this->vendorPath = \sprintf(self::VENDOR_PATH, $bundleName, self::FOLDER_NAME);
    }



    public function addDefaultScripts(): void
    {
        $jsPathCustom   = \sprintf(self::FILES_PATH, $this->pageUtil->getRootPageAlias(), self::FOLDER_NAME);

        if( file_exists($this->basicUtil->getRootDir( true ) . $jsPathCustom . 'functions.js') )
        {
            $GLOBALS['TL_JAVASCRIPT'][] = $jsPathCustom . 'functions.js|static';
        }
    }



// TODO: to PageUtil ??

//    /**
//     * Check if current page has any animations
//     *
//     * @return boolean
//     */
//    public static function hasPageAnimation()
//    {
//        global $objPage;
//
//        $objArticles    = \ArticleModel::findBy( array('published=?', 'pid=?'), array('1', $objPage->id) );
//        $hasAnimation   = false;
//
//        if( $objArticles )
//        {
//            while( $objArticles->next() )
//            {
//                if( $objArticles->addAnimation )
//                {
//                    $hasAnimation = true;
//                    break;
//                }
//
//                $objElements = \ContentModel::findPublishedByPidAndTable( $objArticles->id, \ArticleModel::getTable());
//
//                if( $objElements )
//                {
//                    while( $objElements->next() )
//                    {
//                        if( $objElements->addAnimation )
//                        {
//                            $hasAnimation = true;
//                            break;
//                        }
//                    }
//                }
//
//                if( $hasAnimation )
//                {
//                    break;
//                }
//            }
//        }
//
//        if( !$hasAnimation && (HeaderHelper::isHeaderIsSticky() ||  HeaderHelper::isTopHeaderIsSticky()) )
//        {
//            $hasAnimation = true;
//        }
//
//        if( !$hasAnimation )
//        {
//            $strTable           = \ContentModel::getTable();
//            $objContentElements = \ContentModel::findBy(array($strTable . ".invisible=?", $strTable . ".type=?"), array("", "rsce_feature-box"));
//
//            if( $objContentElements )
//            {
//                while( $objContentElements->next() )
//                {
//                    $cssID = \StringUtil::deserialize($objContentElements->cssID, TRUE);
//
//                    if( preg_match('/bg-parallax/', $cssID[1]) )
//                    {
//                        $hasAnimation = true;
//                    }
//                }
//            }
//        }
//
//        return $hasAnimation;
//    }
//
//
//
//    /**
//     * Check if current page has active isotope script
//     *
//     * @return boolean
//     */
//    public static function hasPageIsotope()
//    {
//        global $objPage;
//
//        $hasIsotope = false;
//
//        if( $objPage->addIsotope )
//        {
//            $hasIsotope = true;
//        }
//        else
//        {
//            $objRootPage = PageHelper::getRootPage();
//
//            if( $objRootPage && $objRootPage->addIsotope )
//            {
//                $hasIsotope = true;
//            }
//        }
//
//        if( !$hasIsotope )
//        {
//            $objArticles = ArticleModel::findBy(['published=?', 'pid=?'], ['1', $objPage->id]);
//
//            if( $objArticles )
//            {
//                while( $objArticles->next() )
//                {
//                    $objElements = ContentModel::findPublishedByPidAndTable( $objArticles->pid, 'tl_article');
//
//                    if( $objElements )
//                    {
//                        while( $objElements->next() )
//                        {
//                            $cssID = StringUtil::deserialize( $objElements->cssID, true );
//
//                            if( false !== strpos($cssID[0], 'listMasonry') )
//                            {
//                                $hasIsotope = true;
//                                break;
//                            }
//                        }
//                    }
//
//                    if( $hasIsotope )
//                    {
//                        break;
//                    }
//                }
//            }
//        }
//
//        return $hasIsotope;
//    }
//
//
//
//    public static function hasPageFullPage( $checkOnlyCurrentPage = false )
//    {
//        global $objPage;
//
//        $hasFullPage = false;
//
//        if( $objPage->enableFullPage )
//        {
//            $hasFullPage = true;
//        }
//        else
//        {
//            if( !$checkOnlyCurrentPage )
//            {
//                $objRootPage = PageHelper::getRootPage();
//
//                if( $objRootPage && $objRootPage->addFullPage )
//                {
//                    $hasFullPage = true;
//                }
//            }
//        }
//
//        return $hasFullPage;
//    }



    public function addScript( string|array $scriptName, bool $addStylesheet = false, bool $includeAdd = false ): void
    {
        if( !is_array($scriptName) )
        {
            $scriptName = array( $scriptName );
        }

        foreach($scriptName as $fileKey => $fileName)
        {
            if( is_numeric($fileKey) )
            {
                $fileKey = $fileName;
            }

            $filePath       = $this->getScriptSource( $fileName, false, true );
            $filePathIntern = $this->getScriptSource( $fileName );

            $rootDir = $this->basicUtil->getRootDir( true );

            //TODO: public path from config.yml file?? "web"?!
            if( file_exists($rootDir . 'web/' . $filePathIntern) )
            {
                $arrAddAfter = array();

                if( $includeAdd )
                {
                    if( is_dir( $rootDir . $filePath . '/add' ) )
                    {
                        $arrFiles = scan( $rootDir . $filePath . '/add' );

                        if( is_array($arrFiles) && count($arrFiles) )
                        {
                            foreach($arrFiles as $strFile )
                            {
                                if( preg_match('/.js.map$/', $strFile) )
                                {
                                    continue;
                                }

                                $fileParts = explode("-", $strFile);

                                if( preg_match('/^b/', $strFile) )
                                {
                                    $num = preg_replace('/^b/', '', $fileParts[0]);

                                    $GLOBALS['TL_JAVASCRIPT'][ $fileKey . '-before-' . $num] = $filePath . '/add/' . $strFile . $this->getScriptMode();
                                }
                                elseif( preg_match('/^a/', $strFile) )
                                {
                                    $arrAddAfter[] = $strFile;
                                }
                            }
                        }
                    }
                }

                $GLOBALS['TL_JAVASCRIPT'][ $fileKey ] = $filePathIntern . $this->getScriptMode();

                if( $includeAdd && count($arrAddAfter) )
                {
                    foreach($arrAddAfter as $strFile )
                    {
                        $fileParts  = explode("-", $strFile);
                        $num        = preg_replace('/^a/', '', $fileParts[0]);

                        $GLOBALS['TL_JAVASCRIPT'][ $fileKey . '-after-' . $num] = $filePath . '/add/' . $strFile . $this->getScriptMode();
                    }
                }

                if( $addStylesheet )
                {
                    //TODO:
//                    StyleSheetHelper::addStylesheet( $fileName );
                }
            }
        }
    }



    public static function addTranslateScript( $scriptName, $language )
    {
        if( !is_array($scriptName) )
        {
            $scriptName = array( $scriptName );
        }

        foreach($scriptName as $fileKey => $fileName)
        {
            if( is_numeric($fileKey) )
            {
                $fileKey = $fileName;
            }

            $filePath = self::getScriptSource( $fileName, false, true );

            if( is_dir(BasicHelper::getRootDir( true ) . $filePath . '/translate') )
            {
                if( file_exists(BasicHelper::getRootDir( true ) . $filePath . '/translate/' . $language . '.js') )
                {
                    $GLOBALS['TL_JAVASCRIPT'][ $fileKey . '-trans-' . $language] = $filePath . '/translate/' . $language . '.js' . self::getScriptMode();
                }
                elseif( file_exists(BasicHelper::getRootDir( true ) . $filePath . '/translate/' . $language . '_' . strtoupper($language) . '.js') )
                {
                    $GLOBALS['TL_JAVASCRIPT'][ $fileKey . '-trans-' . $language] = $filePath . '/translate/' . $language . '_' . strtoupper($language) . '.js' . self::getScriptMode();
                }
            }
        }
    }



    public static function insertScript( $scriptName, $addStylesheet = false )
    {
        if( !is_array($scriptName) )
        {
            $scriptName = array( $scriptName );
        }

        foreach($scriptName as $fileKey => $fileName)
        {
            if( is_numeric($fileKey) )
            {
                $fileKey = $fileName;
            }

            $filePathPublic = self::getScriptSource( $fileName, true, false );

            if( file_exists(BasicHelper::getRootDir( true ) . $filePathPublic) )
            {
                $filePathPublicSRC = self::getScriptSource( $fileName, true, false, true );

                echo '<script src=' . $filePathPublicSRC . '></script>';

                if( $addStylesheet )
                {
                    StyleSheetHelper::addStylesheet( $fileName );
                }
            }
        }
    }



    public function addInternScript( string $scriptName )
    {
        $GLOBALS['TL_JAVASCRIPT']['iido_' . $scriptName ] = BundleConfig::getBundlePath( true ) . self::PATH . $this->getActiveJavascriptLibrary() . '/iido/IIDO.' . ucfirst( $scriptName ) . '.js' . $this->getScriptMode();
    }



    public function addSourceScript( string $scriptName, string|array $sourceScriptName ): void
    {
        if( !is_array($sourceScriptName) )
        {
            $sourceScriptName = array( $sourceScriptName );
        }

        $filePathIntern = $this->getScriptSource( $scriptName, false, true ); // . '/src/';
        $filePath       = $this->getScriptSource( $scriptName, true, true ); // . '/src/';

        foreach( $sourceScriptName as $srcKey => $srcFileName )
        {
            if( file_exists($this->basicUtil->getRootDir(true) . $filePathIntern . $srcFileName . '.min.js') )
            {
                if( is_numeric($srcKey) )
                {
                    $srcKey = $scriptName . '_' . $srcFileName;
                }

                $GLOBALS['TL_JAVASCRIPT'][ $srcKey ] = $filePath. $srcFileName . '.min.js' . $this->getScriptMode();
            }
        }
    }



    public function getScriptSource( string $scriptName, bool $public = true, bool $withoutFile = false, bool $publicWithoutWeb = false ): string
    {
        $strPath    = $this->vendorPath; // . \sprintf(self::PATH, self::FOLDER_NAME);
        $subFolder  = 'library';

        $folderVersion = $this->getScriptVersion( $scriptName );
        $rootDir = $this->basicUtil->getRootDir( true );

        if( !is_dir( $rootDir . $strPath . $subFolder . '/' . $scriptName . '/' . $folderVersion) )
        {
            $subFolder = $this->getActiveJavascriptLibrary();
        }

        $arrFiles = Folder::scan( $rootDir . $strPath . $subFolder . '/' . $scriptName . '/' . $folderVersion );
        $fileName = '';

        foreach($arrFiles as $strFile)
        {
            if( preg_match('/.min.js$/', $strFile) && preg_match('/' . $scriptName . '/', $strFile) )
            {
                $fileName = $strFile;
                break;
            }
        }

        return ($public ? $this->bundlePath : $this->vendorPath) . $subFolder . '/' . $scriptName . '/' . $folderVersion . ($withoutFile ? '' : '/' . $fileName);
//        return BundleConfig::getBundlePath( $public, !$publicWithoutWeb ) . ($public ? '/' . self::FOLDER_NAME : \sprintf(self::PATH, self::FOLDER_NAME)) . $subFolder . '/' . $scriptName . '/' . $folderVersion . ($withoutFile ? '' : '/' . $fileName);
//        return BundleConfig::getBundlePath( $public ) . ($public ? self::$scriptPathPublic : self::$scriptPath) . $subFolder . '/' . $scriptName . '/' . $folderVersion . ($withoutFile ? '' : '/' . $fileName);
    }



    /**
     * Get script version
     *
     * @param string $scriptName
     *
     * @return mixed|null
     */
    public function getScriptVersion( string $scriptName )
    {
        $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();
        $scriptVersion      = \Config::get( $tableFieldPrefix . 'script' . ucfirst($scriptName) );

        if( !$scriptVersion )
        {
            $strPath    = $this->vendorPath;
            $subFolder  = 'library';

            $rootDir = $this->basicUtil->getRootDir( true );

            if( !is_dir( $rootDir . $strPath . $subFolder . '/' . $scriptName . '/' . $scriptVersion) )
            {
                $subFolder = $this->getActiveJavascriptLibrary();
            }

            $arrFolders = scan( $rootDir . $strPath . $subFolder . '/' . $scriptName );

            if( in_array('addons', $arrFolders) )
            {
                unset( $arrFolders[ array_search('addons', $arrFolders ) ] );

                $arrFolders = array_values($arrFolders);
            }

            if( count($arrFolders) > 1 )
            {
                foreach($arrFolders as $folder )
                {
                    if( $scriptVersion )
                    {
                        if( version_compare($folder, $scriptVersion, '>') )
                        {
                            $scriptVersion = $folder;
                        }
                    }
                    else
                    {
                        $scriptVersion = $folder;
                    }
                }
            }
            else
            {
                $scriptVersion = $arrFolders[0];
            }
        }

        return $scriptVersion;
    }



    public function getActiveJavascriptLibrary(): string|bool
    {
        global $objPage;

        $objLayout  = $this->pageUtil->getPageLayout( $objPage );

        $jquery     = $objLayout->addJQuery;
        $mootools   = $objLayout->addMooTools;

        if( $jquery )
        {
            return 'jquery';
        }

        if( $mootools )
        {
            return 'mootools';
        }

        return false;
    }



    public function getScriptMode(): string
    {
        global $objPage;

        $objLayout = $this->pageUtil->getPageLayout( $objPage );

        return ($objLayout->combineScripts ? self::MODE : '');
    }

}
