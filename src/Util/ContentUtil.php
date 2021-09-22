<?php


namespace IIDO\UtilsBundle\Util;


use Contao\ContentModel;
use Contao\ModuleModel;


class ContentUtil
{

    public function getContentElementData( ContentModel $contentModel ): array
    {
        $type   = $contentModel->type;
        $prefix = $contentModel->typePrefix;

        $className      = $type;
        $tag            = 'div';
        $insideClass    = 'element';

        if( 'module' === $type )
        {
            $module = ModuleModel::findByPk( $contentModel->module );

            $prefix     = 'mod_';
            $className  = $module->type;

            if( 'navigation' === $module->type )
            {
                $insideClass = 'navigation';
                $tag = 'nav';
            }
        }

        if( 'rocksolid_slider' === $type )
        {
            $prefix = 'mod_';
        }

        $elementType    = $prefix . $className;

        return [$elementType, $insideClass, $tag];
    }



    public function addClassesToContentElement( string $buffer, ContentModel $contentModel, array $elementClasses, array $elementData ): string
    {
        list( $elementType, $insideClass, $tag) = $elementData;

        $buffer = \preg_replace('/<' . $tag . '([A-Za-z0-9\s\-,;.:\(\)?!_\{\}="\/]+)class="' . $elementType . '/', '<' . $tag . '$1class="' . $elementType . ($elementClasses ? ' ' . implode(' ', $elementClasses) : ''), $buffer, 1);

        return $buffer;
    }



    public function stringContains( string $string, string|array $contains ): bool
    {
        if( is_array($contains) )
        {
            $containWord = false;

            foreach( $contains as $contain )
            {
                if( str_contains($string, $contain) )
                {
                    $containWord = true;
                    break;
                }
            }

            return $containWord;
        }
        else
        {
            return str_contains($string, $contains);
        }
    }
}
