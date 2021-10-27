<?php
/*******************************************************************
 * (c) 2021 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\UtilsBundle\Helper;


class DcaHelper
{
    public static function addLegend( string|array $name, string $parent, string $position, string|array $palettes, string $table ): void
    {
        if( 'all' === $palettes )
        {
            $palettes = \array_keys($GLOBALS['TL_DCA'][ $table ]['palettes']);
            $pos = \array_search('__selector__', $palettes);

            unset( $palettes[ $pos ] );
        }

        if( !\is_array($palettes) )
        {
            $palettes = [$palettes];
        }

        if( !\is_array($name) )
        {
            $name = [$name];
        }

        if( !\preg_match('/_legend$/', $parent) )
        {
            $parent = $parent . '_legend';
        }

        foreach( $palettes as $paletteName )
        {
            $palette = $GLOBALS['TL_DCA'][ $table ]['palettes'][ $paletteName ];

            foreach( $name as $legendName )
            {
                $legendParts = explode(':', $legendName);
                $legendName     = $legendParts[0];
                $legendOption   = $legendParts[1];

                if( !\preg_match('/_legend$/', $legendName) )
                {
                    $legendName = $legendName . '_legend';
                }

                if( $legendOption )
                {
                    $legendName .= ':' . $legendOption;
                }

                $legendName = '{' . $legendName . '},;';

                $before = '';
                $after  = '';

                if( 'before' === $position )
                {
                    $before = $legendName;
                }
                else if( 'after' === $position )
                {
                    $after = $legendName;
                }

                $palette = \preg_replace('/\{' . $parent . '([A-Za-z0-9\-:]{0,})\},([A-Za-z0-9\-_,]{0,});/', $before . '{' . $parent . '$1},$2;' . $after, $palette);
            }

            $GLOBALS['TL_DCA'][ $table ]['palettes'][ $paletteName ] = $palette;
        }
    }



    public static function addField( string|array $name, string $parent, string $position, string|array $palettes, string $table )
    {
        if( 'all' === $palettes )
        {
            $palettes = array_keys($GLOBALS['TL_DCA'][ $table ]['palettes']);
            $pos = array_search('__selector__', $palettes);

            unset( $palettes[ $pos ] );
        }

        if( !is_array($palettes) )
        {
            $palettes = [$palettes];
        }

        if( is_array($name) )
        {
            $name = implode(',', $name);
        }

        foreach( $palettes as $paletteName )
        {
            $palette = $GLOBALS['TL_DCA'][ $table ]['palettes'][ $paletteName ];

            $replacement = ',' . $parent;

            if( 'before' === $position )
            {
                $replacement = ',' . $name . $replacement . '$1';
            }
            else if( 'after' === $position )
            {
                $replacement = $replacement . ',' . $name . '$1';
            }

            $palette = preg_replace('/,' . $parent . '(,|;)/', $replacement, $palette);

            $GLOBALS['TL_DCA'][ $table ]['palettes'][ $paletteName ] = $palette;
        }
    }



    public static function addFieldToLegend( string|array $name, string $legend, string $position, string|array $palettes, string $table ): void
    {
        if( 'all' === $palettes )
        {
            $palettes = array_keys($GLOBALS['TL_DCA'][ $table ]['palettes']);
            $pos = array_search('__selector__', $palettes);

            unset( $palettes[ $pos ] );
        }

        if( !is_array($palettes) )
        {
            $palettes = [$palettes];
        }

        if( is_array($name) )
        {
            $name = implode(',', $name);
        }

        if( !\preg_match('/_legend$/', $legend) )
        {
            $legend = $legend . '_legend';
        }

        foreach( $palettes as $paletteName )
        {
            $palette = $GLOBALS['TL_DCA'][ $table ]['palettes'][ $paletteName ];

            if( 'prepand' === $position )
            {
                $palette = \preg_replace('/\{' . $legend . '([A-Za-z0-9\-_,]{0,})\}/', '{' . $legend . '$1},' . $name, $palette);
            }

            $GLOBALS['TL_DCA'][ $table ]['palettes'][ $paletteName ] = $palette;
        }
    }



    public static function addFieldToSubpalette( string|array $name, string $parent, string $position, string|array $palettes, string $table ): void
    {
        if( 'all' === $palettes )
        {
            $palettes = array_keys($GLOBALS['TL_DCA'][ $table ]['subpalettes']);
        }

        if( is_array($name) )
        {
            $name = implode(',', $name);
        }

        if( !is_array($palettes) )
        {
            $palettes = [$palettes];
        }

        foreach( $palettes as $paletteName )
        {
            $palette = $GLOBALS['TL_DCA'][ $table ]['subpalettes'][ $paletteName ];

            $replacement = ',' . $parent;

            if( 'before' === $position )
            {
                $replacement = ',' . $name . $replacement;
            }
            else if( 'after' === $position )
            {
                $replacement = $replacement . ',' . $name;
            }

            $palette = str_replace(',' . $parent, $replacement, $palette);

            $GLOBALS['TL_DCA'][ $table ]['subpalettes'][ $paletteName ] = $palette;
        }
    }



    public static function replacePaletteFields( string $parent, string $replacement, string|array $palettes, string $table ): void
    {
        if( 'all' === $palettes )
        {
            $palettes = array_keys($GLOBALS['TL_DCA'][ $table ]['palettes']);
            $pos = array_search('__selector__', $palettes);

            unset( $palettes[ $pos ] );
        }

        if( !is_array($palettes) )
        {
            $palettes = [$palettes];
        }

        foreach( $palettes as $paletteName )
        {
            $palette = $GLOBALS['TL_DCA'][ $table ]['palettes'][ $paletteName ];

            $palette = \preg_replace('/' . $parent . '/', $replacement, $palette);

            $GLOBALS['TL_DCA'][ $table ]['palettes'][ $paletteName ] = $palette;
        }
    }
}
