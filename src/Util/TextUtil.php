<?php


namespace IIDO\UtilsBundle\Util;


use Contao\ContentModel;
use Contao\StringUtil;


class TextUtil
{

    public function renderHeadline( string $buffer, ContentModel $contentModel, string $type ): string
    {
        $strTopHeadline = '';
        $arrHeadline    = StringUtil::deserialize($contentModel->headline, true);

        $strHeadline    = $arrHeadline['value'];
        $strUnit        = $arrHeadline['unit'];

        $topHeadline    = $contentModel->topHeadline;

        if( $topHeadline )
        {
            $strTopHeadline = '<div class="top-headline">' . $topHeadline . '</div>';
        }

        if( !$strHeadline && !$topHeadline )
        {
            return $buffer;
        }

        $buffer = str_replace('<' . $strUnit . '>' . $strHeadline, $strTopHeadline . '<' . $strUnit . ' class="headline">' . $strHeadline, $buffer, $count);

        if( !$count )
        {
            if( str_contains($buffer, 'class="headline"') && !str_contains($buffer, $strTopHeadline) )
            {
                $buffer = str_replace('<' . $strUnit . ' class="headline">' . $strHeadline, $strTopHeadline . '<' . $strUnit . ' class="headline">' . $strHeadline, $buffer, $count);
            }
        }

        return $buffer;
    }



    public function renderText( string $buffer ): string
    {
        $buffer = \preg_replace('/\^([0-9]+)/', '<sup>$1</sup>', $buffer);

        return $buffer;
    }
}
