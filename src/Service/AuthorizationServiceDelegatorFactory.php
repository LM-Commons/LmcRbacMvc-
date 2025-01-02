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

namespace Lmc\Rbac\Mvc\Service;

//use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Lmc\Rbac\Exception\RuntimeException;
use Lmc\Rbac\Mvc\Service\AuthorizationService;
use Lmc\Rbac\Mvc\Service\AuthorizationServiceAwareInterface;
use Psr\Container\ContainerInterface;

use function call_user_func;

/**
 * Delegator factory for classes implementing AuthorizationServiceAwareInterface
 */
class AuthorizationServiceDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        ?array $options = null
    ): AuthorizationServiceAwareInterface {
        $instanceToDecorate = call_user_func($callback);

        if (! $instanceToDecorate instanceof AuthorizationServiceAwareInterface) {
            throw new RuntimeException("The service $name must implement AuthorizationServiceAwareInterface.");
        }
        $authorizationService = $container->get(AuthorizationService::class);
        $instanceToDecorate->setAuthorizationService($authorizationService);

        return $instanceToDecorate;
    }

    /** TODO remove */
    // phpcs:disable
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        return $this($serviceLocator, $requestedName, $callback);
    }
    // phpcs:enable
}
