<?php
declare(strict_types=1);

namespace IIDO\UtilsBundle;


use Symfony\Component\HttpKernel\Bundle\Bundle;


class IIDOUtilsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
