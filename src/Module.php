<?php

declare(strict_types=1);

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Lmc\Rbac\Mvc;

use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Module class for LmcRbacMvc
 */
class Module implements BootstrapListenerInterface, ConfigProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $e): void
    {
        /** @var Application $application */
        $application    = $e->getTarget();
        $serviceManager = $application->getServiceManager();
        $eventManager   = $application->getEventManager();

        /** @var GuardInterface[]|array $guards */
        $guards = $serviceManager->get('Lmc\Rbac\Mvc\Guards');

        // Register listeners, if any
        foreach ($guards as $guard) {
            $guard->attach($eventManager);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        $configProvider = new ConfigProvider();
        return [
            'service_manager'    => $configProvider->getDependencies(),
            'view_helpers'       => $configProvider->getViewHelperConfig(),
            'controller_plugins' => $configProvider->getControllerPluginConfig(),
            'view_manager'       => $configProvider->getViewManagerConfig(),
            'lmc_rbac'           => $configProvider->getModuleConfig(),
        ];
    }
}
