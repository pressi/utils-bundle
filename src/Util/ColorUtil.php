<?php
/*******************************************************************
 * (c) 2021 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\UtilsBundle\Util;


use Contao\DataContainer;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\ORM\EntityManagerInterface;
use IIDO\CoreBundle\Entity\WebsiteColorEntity;
use IIDO\CoreBundle\Repository\WebsiteColorRepository;


class ColorUtil
{
    protected EntityManagerInterface $entityManager;


    public function __construct( EntityManagerInterface $entityManager )
    {
        $this->entityManager = $entityManager;
    }


//TODO: function back to core bundle??
    public function getWebsiteColors( ?DataContainer $dc ): array
    {
        $colors = [];

        if( $dc )
        {
            if( 'tl_article' === $dc->table )
            {
                $page = PageModel::findByPk( $dc->activeRecord->pid );

                if( $page->pid > 0 )
                {
//                    $page = PageModel::findByPk( $page->rootId );

                    /** @var $repo WebsiteColorRepository */
                    $repo = $this->entityManager->getRepository(WebsiteColorEntity::class);
                    $pageColors = $repo->findByPage( $page->rootId );
//                    $this->entityManager->find('WebsiteColorEntity', );

//                    $pageColors = StringUtil::deserialize($page->colors, true);


                    if( count($pageColors) )
                    {
                        foreach( $pageColors as $pageColor )
                        {
                            if( strlen(trim($pageColor->getColor())) )
                            {
                                $colors[ $pageColor->getId() ] = $pageColor->getLabel() ? $pageColor->getLabel() . ' (#' . $pageColor->getColor() . ')' : '#' . $pageColor->getColor();
                            }
                        }
                    }
                }
            }
        }

        return $colors;
    }



    public function compileColor( mixed $color, bool $blnWriteToFile = false, array $vars = array() ): string
    {
        if( !is_array($color) && str_starts_with($color, '#') )
        {
            return $color;
        }

        $checkColor = StringUtil::deserialize($color);

        if( is_array($checkColor) && is_string($color) )
        {
            $color = $checkColor;
        }

        if( !is_array($color) )
        {
            return ((strlen($color)) ? '#' . $this->shortenHexColor($color) : 'transparent');
        }
        elseif( !isset($color[1]) || empty($color[1]) )
        {
            if($color[0] == "")
            {
                if( $color[2] )
                {
                    $strColor = $color[2];

                    if( !str_starts_with($strColor, '#') )
                    {
                        $strColor = '#' . $strColor;
                    }

                    return $strColor;
                }

                return "transparent";
            }

            return '#' . $this->shortenHexColor($color[0]);
        }
        else
        {
            if( $color[2] && !$color[0] )
            {
                $color[0] = $color[2];
            }

            return 'rgba(' . implode(',', $this->convertHexColor($color[0], $blnWriteToFile, $vars)) . ','. ($color[1] / 100) .')';
        }
    }



    public function shortenHexColor( string $color ): string
    {
        if ($color[0] == $color[1] && $color[2] == $color[3] && $color[4] == $color[5])
        {
            return $color[0] . $color[2] . $color[4];
        }

        return $color;
    }



    public static function convertHexColor( string $color, bool $blnWriteToFile = false, array $vars = array()): array
    {
        $color = preg_replace('/^\#/', '', $color);

        // Support global variables
        if (strncmp($color, '$', 1) === 0)
        {
            if (!$blnWriteToFile)
            {
                return array($color);
            }
            else
            {
                $color = str_replace(array_keys($vars), array_values($vars), $color);
            }
        }

        $rgb = array();

        // Try to convert using bitwise operation
        if (strlen($color) == 6)
        {
            $dec = hexdec($color);
            $rgb['red']     = 0xFF & ($dec >> 0x10);
            $rgb['green']   = 0xFF & ($dec >> 0x8);
            $rgb['blue']    = 0xFF & $dec;
        }

        // Shorthand notation
        elseif (strlen($color) == 3)
        {
            $rgb['red']     = hexdec(str_repeat(substr($color, 0, 1), 2));
            $rgb['green']   = hexdec(str_repeat(substr($color, 1, 1), 2));
            $rgb['blue']    = hexdec(str_repeat(substr($color, 2, 1), 2));
        }

        return $rgb;
    }



    public function renderColorConfig( string|array $arrColor ): string
    {
        if( is_numeric($arrColor) )
        {
            $color = $this->entityManager->find( WebsiteColorEntity::class, $arrColor);

            return serialize([$color->getColor()]);
        }

        if( !is_array($arrColor) )
        {
            $arrColor = StringUtil::deserialize( $arrColor, TRUE );
        }

        $strColor = self::compileColor( $arrColor );

        if( $strColor !== "transparent" )
        {
            if( $arrColor[0] === "" && $arrColor[2] )
            {
                $arrColor[0] = preg_replace(['/^#/'], '', $arrColor[2]);
            }
        }

        unset($arrColor[2]);

        return serialize($arrColor);
    }
}
