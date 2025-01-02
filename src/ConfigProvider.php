<?php

declare(strict_types=1);

namespace Lmc\Rbac\Mvc;

use Lmc\Rbac\Mvc\Guard\GuardPluginManager;
use Lmc\Rbac\Mvc\Guard\GuardPluginManagerFactory;
use Lmc\Rbac\Mvc\Guard\GuardsFactory;
use Lmc\Rbac\Mvc\Identity\AuthenticationIdentityProvider;
use Lmc\Rbac\Mvc\Identity\AuthenticationIdentityProviderFactory;
use Lmc\Rbac\Mvc\Mvc\Controller\Plugin\IsGranted;
use Lmc\Rbac\Mvc\Mvc\Controller\Plugin\IsGrantedPluginFactory;
use Lmc\Rbac\Mvc\Options\ModuleOptions;
use Lmc\Rbac\Mvc\Options\ModuleOptionsFactory;
use Lmc\Rbac\Mvc\Service\AuthorizationService;
use Lmc\Rbac\Mvc\Service\AuthorizationServiceFactory;
use Lmc\Rbac\Mvc\Service\RoleService;
use Lmc\Rbac\Mvc\Service\RoleServiceFactory;
use Lmc\Rbac\Mvc\View\Helper\HasRole;
use Lmc\Rbac\Mvc\View\Helper\HasRoleViewHelperFactory;
use Lmc\Rbac\Mvc\View\Helper\IsGrantedViewHelperFactory;
use Lmc\Rbac\Mvc\View\Strategy\RedirectStrategy;
use Lmc\Rbac\Mvc\View\Strategy\RedirectStrategyFactory;
use Lmc\Rbac\Mvc\View\Strategy\UnauthorizedStrategy;
use Lmc\Rbac\Mvc\View\Strategy\UnauthorizedStrategyFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'       => $this->getDependencies(),
            'view_helpers'       => $this->getViewHelperConfig(),
            'controller_plugins' => $this->getControllerPluginConfig(),
            'view_manager'       => $this->getViewManagerConfig(),
            'lmc_rbac'           => $this->getModuleConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                /* Factories that do not map to a class */
                'Lmc\Rbac\Mvc\Guards' => GuardsFactory::class,

                /* Factories that map to a class */
                GuardPluginManager::class             => GuardPluginManagerFactory::class,
                AuthenticationIdentityProvider::class => AuthenticationIdentityProviderFactory::class,
                ModuleOptions::class                  => ModuleOptionsFactory::class,
                AuthorizationService::class           => AuthorizationServiceFactory::class,
                RoleService::class                    => RoleServiceFactory::class,
                RedirectStrategy::class               => RedirectStrategyFactory::class,
                UnauthorizedStrategy::class           => UnauthorizedStrategyFactory::class,
            ],
        ];
    }

    public function getModuleConfig(): array
    {
        return [
            // Guard plugin manager
            'guard_manager' => [],
        ];
    }

    public function getControllerPluginConfig(): array
    {
        return [
            'factories' => [
                IsGranted::class => IsGrantedPluginFactory::class,
            ],
            'aliases'   => [
                'isGranted' => IsGranted::class,
            ],
        ];
    }

    public function getViewHelperConfig(): array
    {
        return [
            'factories' => [
                \Lmc\Rbac\Mvc\View\Helper\IsGranted::class => IsGrantedViewHelperFactory::class,
                HasRole::class                             => HasRoleViewHelperFactory::class,
            ],
            'aliases'   => [
                'isGranted' => \Lmc\Rbac\Mvc\View\Helper\IsGranted::class,
                'hasRole'   => HasRole::class,
            ],
        ];
    }

    public function getViewManagerConfig(): array
    {
        return [
            'template_map' => [
                'error/403' => __DIR__ . '/../view/error/403.phtml',
            ],
        ];
    }
}
