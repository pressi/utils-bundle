<?php


namespace IIDO\UtilsBundle\Util;


use Contao\ContentModel;
use Contao\StringUtil;


class TextUtil
{

    public function renderHeadline( string $buffer, ContentModel $contentModel, string $type ): string
    {
        $arrHeadline    = StringUtil::deserialize($contentModel->headline, true);

        $strHeadline    = $arrHeadline['value'];
        $strUnit        = $arrHeadline['unit'];

        $buffer = str_replace('<' . $strUnit . '>', '<' . $strUnit . ' class="headline">', $buffer);

        return $buffer;
    }
}
