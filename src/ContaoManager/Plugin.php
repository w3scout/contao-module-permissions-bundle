<?php
namespace SeptemberWerbeagentur\ContaoModulePermissionsBundle\ContaoManager;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use SeptemberWerbeagentur\ContaoModulePermissionsBundle\ContaoModulePermissionsBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoModulePermissionsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
