<?php


namespace IIDO\UtilsBundle\Util;


use Contao\FilesModel;
use Contao\Model;
use Contao\StringUtil;
use Contao\StyleSheets;
use Doctrine\ORM\EntityManagerInterface;


class StylesUtil
{
    const FOLDER    = 'styles';
    const MODE      = '||static';

    const PATH          = 'files/%s/%s/';
    const BUNDLE_PATH   = '/Resources/public/%s/';
    const PUBLIC_PATH   = '/%s/';

    const VARIABLES_FILE_PATH           = self::PATH . '_variables.scss';
    const VARIABLES_CONFIG_FILE_PATH    = 'files/config/styles/variables_%s.scss'; // TODO: ???

    const MASTER_FILE_PATH      = 'files/master/scss/master.scss';


    protected BasicUtil $basicUtil;

    protected ColorUtil $colorUtil;

    protected EntityManagerInterface $entityManager;



    public function __construct( BasicUtil $basicUtil, ColorUtil $colorUtil, EntityManagerInterface $entityManager )
    {
        $this->basicUtil = $basicUtil;
        $this->colorUtil = $colorUtil;
        $this->entityManager = $entityManager;
    }



    public function getStyles( Model $element, bool $onlyOwnStyles = false, bool $returnAsArray = false, bool $writeInFile = true, string $selector = '' ): bool|array|string
    {
        $ownStyles          = [];
        $addBackgroundImage = (bool) ($element->bgImage);
        $backgroundSize     = StringUtil::deserialize($element->bgSize, true);

//        if( $addBackgroundImage && !count($ownStyles) && !$element->bgRepeat && !$element->bgPosition )
        if( $addBackgroundImage && !$element->bgRepeat && !$element->bgPosition )
        {
            $backgroundSize[2]      = 'cover';
            $element->bgRepeat      = 'no-repeat';
            $element->bgPosition    = 'center center';
        }

        if( $addBackgroundImage && is_array($backgroundSize) && strlen($backgroundSize[2]) && $backgroundSize[2] != '-' )
        {
            $bgSize = $backgroundSize[2];

            if( $backgroundSize[2] == 'own' )
            {
                unset($backgroundSize[2]);
                $bgSize = implode(" ", $backgroundSize);
            }

            $bgSize = preg_replace('/^_/', '', $bgSize);

            $ownStyles[] = '-webkit-background-size:' . $bgSize . ';-moz-background-size:' . $bgSize . ';-o-background-size:' . $bgSize . ';background-size:' . $bgSize . ';';
        }

        if( $addBackgroundImage && $element->bgAttachment )
        {
            $ownStyles[] = 'background-attachment:' . $element->bgAttachment . ';';
        }

        if( $onlyOwnStyles )
        {
            return $returnAsArray ? $onlyOwnStyles : implode("", $ownStyles);
        }

        $rootDir    = $this->basicUtil->getRootDir();
        $strImage   = '';

        if( $addBackgroundImage )
        {
            $image   = FilesModel::findByUuid( $element->bgImage );

            if( $image && file_exists($rootDir . '/' . $image->path) )
            {
                $strImage = $image->path;
            }
        }

        $bgColor = $element->bgColor;

        if( $element->addOwnBGColor )
        {
            $bgColor = $element->ownBGColor;
        }

        $arrStyles = array
        (
            'background'        => TRUE,
            'bgcolor'           => $this->colorUtil->renderColorConfig( $bgColor ),

            'bgimage'           => $strImage,
            'bgrepeat'          => $addBackgroundImage ? $element->bgRepeat     : '',
            'bgposition'        => $addBackgroundImage ? $element->bgPosition   : '',

            'gradientAngle'     => $element->gradientAngle,
            'gradientColors'    => $element->gradientColors
        );

        if( count($ownStyles) )
        {
            $arrStyles['own'] = implode("", $ownStyles);
        }

        if( strlen($selector) )
        {
            $arrStyles['selector'] = $selector;
        }

        if( !$returnAsArray )
        {
            $objStyleSheets     = new StyleSheets();
            $arrStyles          = $objStyleSheets->compileDefinition($arrStyles, $writeInFile);

            $arrStyles          = \preg_replace('/^\{\}$/', '', trim($arrStyles));
        }

        return $arrStyles;
    }
}
