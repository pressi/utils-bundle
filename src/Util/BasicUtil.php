<?php
/*******************************************************************
 * (c) 2021 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\UtilsBundle\Util;


use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class BasicUtil
{
    private RequestStack $requestStack;


    private ScopeMatcher $scopeMatcher;


    private ParameterBagInterface $params;



    public function __construct( RequestStack $requestStack, ScopeMatcher $scopeMatcher, ParameterBagInterface $params )
    {
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;

        $this->params = $params;
    }



    public function getRootDir( $includeSlash = false ): string
    {
        return $this->params->get('kernel.project_dir') . ($includeSlash ? '/' : '');
    }



    public function getLanguage(): string
    {
        return $this->requestStack->getCurrentRequest()->getLocale();
    }



    public function getContaoVersion(): string
    {
        $packages = $this->params->get('kernel.packages');
        return $packages['contao/core-bundle'];
    }



    public function isBackend(): bool
    {
        return $this->scopeMatcher->isBackendRequest( $this->requestStack->getCurrentRequest() );
    }



    public function isFrontend(): bool
    {
        return $this->scopeMatcher->isFrontendRequest( $this->requestStack->getCurrentRequest() );
    }



    // TODO: other util ????
    public function renderPhoneNumber( $number ): string
    {
        $number = \str_replace(['(0)', '-', '/'], '', $number);
        $number = \preg_replace('/([\s]+)/', '', $number);

        return $number;
    }
}
