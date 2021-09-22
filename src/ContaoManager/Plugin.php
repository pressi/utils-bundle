<?php
declare(strict_types=1);

namespace IIDO\UtilsBundle\ContaoManager;


use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use IIDO\UtilsBundle\IIDOUtilsBundle;


class Plugin implements BundlePluginInterface
{

    /**
     * @inheritDoc
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            (new BundleConfig(IIDOUtilsBundle::class))->setLoadAfter([ContaoCoreBundle::class])
        ];
    }
}
