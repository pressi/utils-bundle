<?php
/*******************************************************************
 * (c) 2021 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\UtilsBundle\Util;


use Contao\CoreBundle\ServiceAnnotation\Page;
use Contao\Environment;
use Contao\Input;
use Contao\LayoutModel;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use IIDO\CoreBundle\Config\BundleConfig;


class PageUtil
{
    const FILES_SCRIPT_PATH  = 'files/%s/scripts/';


    private BasicUtil $basicUtil;



    public function __construct( BasicUtil $basicUtil )
    {
        $this->basicUtil = $basicUtil;
    }


    // TODO: to StylesUtil ??
    public function addDefaultPageStyleSheets()
    {
        global $objPage;
        /** @var PageModel $objPage */

        $rootAlias          = $objPage->rootAlias;
        $rootPath           = $this->basicUtil->getRootDir( true );
        $customStyleFiles   = 'fonts,icons,animate,core,buttons,form,forms,layout,hamburgers,hamburgers.min,navigation,content,style,styles,page,sidekick,responsive';

        $stylesPathPublic   = BundleConfig::getBundlePath( true ) . sprintf(StylesUtil::PUBLIC_PATH, StylesUtil::FOLDER);

        $path               = sprintf(StylesUtil::PATH, $rootAlias, StylesUtil::FOLDER);
        $mainFilePath       = $path . 'main.scss';
        $includeMainFile    = file_exists($rootPath . $mainFilePath);

        if( !file_exists($rootPath . sprintf(StylesUtil::VARIABLES_CONFIG_FILE_PATH, $rootAlias)) )
        {
            $arrConfigPath = explode('/', StylesUtil::VARIABLES_CONFIG_FILE_PATH);

            if( !is_dir($rootPath . 'files/' . $arrConfigPath[1]) )
            {
                mkdir( $rootPath . 'files/' . $arrConfigPath[1]);
                mkdir( $rootPath . 'files/' . $arrConfigPath[1] . '/' . $arrConfigPath[2]);
            }

            if( !is_dir($rootPath . 'files/' . $arrConfigPath[1] . '/' . $arrConfigPath[2]) )
            {
                mkdir( $rootPath . 'files/' . $arrConfigPath[1] . '/' . $arrConfigPath[2]);
            }

            touch( $rootPath . sprintf(StylesUtil::VARIABLES_CONFIG_FILE_PATH, $rootAlias) );
        }

        if( $includeMainFile )
        {
            $arrCustomStyleFiles = StringUtil::trimsplit(',', $customStyleFiles);

            if( count($arrCustomStyleFiles) )
            {
                foreach( $arrCustomStyleFiles as $strFileName )
                {
                    if( !preg_match('/.css$/', $strFileName) )
                    {
                        $strFileName .= '.css';
                    }

                    $keyName = preg_replace(['/.css$/'], [''], $strFileName);

                    if( file_exists($rootPath . $path . $strFileName) )
                    {
                        $GLOBALS['TL_USER_CSS']['iido_custom_' . $keyName] = $stylesPathPublic . $strFileName .  StylesUtil::MODE;
                    }

                    $strSCSSFileName = preg_replace('/.css$/', '.scss', $strFileName);

                    if( file_exists($rootPath . $path . $strSCSSFileName) )
                    {
                        $GLOBALS['TL_USER_CSS']['iido_custom_style_' . $keyName] = $stylesPathPublic . $strSCSSFileName . StylesUtil::MODE;
                    }
                }
            }

            $GLOBALS['TL_USER_CSS']['iido_main_self'] = $mainFilePath . StylesUtil::MODE;
        }
        //TODO:
//        else
//        {
//            $GLOBALS['TL_USER_CSS']['iido_main_master'] = sprintf(StylesUtil::PATH, 'master', 'scss') . 'onlyMasters.scss' . StylesUtil::MODE;
//        }

        if( file_exists($rootPath . sprintf(StylesUtil::PATH, $rootAlias, StylesUtil::FOLDER) . 'css/sidekick.css') )
        {
            $GLOBALS['TL_USER_CSS']['css_sidekick'] = sprintf(StylesUtil::PATH, $rootAlias, StylesUtil::FOLDER) . 'css/sidekick.css' . StylesUtil::MODE;
        }
    }



    // TODO: to ScriptUtil ??
    public function addDefaultPageScripts()
    {
        $pageUtil = System::getContainer()->get('iido.utils.page');

        $jsPathCustom   = sprintf(self::FILES_SCRIPT_PATH, $pageUtil->getRootPageAlias());

        if( file_exists($this->basicUtil->getRootDir( true ) . $jsPathCustom . 'functions.js') )
        {
            $GLOBALS['TL_JAVASCRIPT']['files_functions'] = $jsPathCustom . 'functions.js|static';
        }

        $GLOBALS['TL_JAVASCRIPT']['iido_core_content'] = 'bundles/iidocore/scripts/IIDO.Core.Content.js|static';
    }



    public function getRootPageAlias( bool $langFallback = false ): string
    {
        //TODO:
        if( $this->basicUtil->isFrontend() )
        {
            global $objPage;

            return $objPage->rootAlias;
        }

        return '';
    }



    public function getRootPage( int|PageModel $page = null, bool $langFallback = false ): ?PageModel
    {
        if( $page )
        {
            if( !$page instanceof PageModel )
            {
                $page = PageModel::findByPk( $page );
            }
        }
        else
        {
            global $objPage;

            $page = $objPage;
        }

        if( $page->pid > 0 )
        {
            $rootPage = PageModel::findByPk( $page->rootId );
        }
        else
        {
            $rootPage = $page;
        }

        if( $langFallback )
        {
            //TODO:
        }

        return $rootPage;
    }



    public function getPageLayout( Model $pageModel = null ): Model|bool
    {
        if( $pageModel === null )
        {
            return false;
        }

        $blnMobile  = ($pageModel->mobileLayout && Environment::get('agent')->mobile);

        // Override the autodetected value
        if( Input::cookie('TL_VIEW') === 'mobile' && $pageModel->mobileLayout )
        {
            $blnMobile = true;
        }
        elseif( Input::cookie('TL_VIEW') === 'desktop' )
        {
            $blnMobile = false;
        }

        $intId      = $blnMobile ? $pageModel->mobileLayout : $pageModel->layout;
        $objLayout  = LayoutModel::findByPk( $intId );

        // Die if there is no layout
        if ($objLayout === null)
        {
            $objLayout = false;

            if( $pageModel->pid > 0 )
            {
                $objParentPage  = PageModel::findByPk( $pageModel->pid );
                $objLayout      = $this->getPageLayout( $objParentPage );
            }
        }

        return $objLayout;
    }
}
