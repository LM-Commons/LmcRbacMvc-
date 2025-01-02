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

namespace LmcTest\Rbac\Mvc\Service;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Lmc\Rbac\Mvc\Service\AuthorizationServiceFactory;
use Lmc\Rbac\Mvc\Service\RoleService;
use Lmc\Rbac\Service\AuthorizationService;
use Lmc\Rbac\Service\AuthorizationServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Lmc\Rbac\Mvc\Service\AuthorizationServiceFactory
 */
class AuthorizationServiceFactoryTest extends TestCase
{
    /**
     * Test the default case
     */
    public function testFactory()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function ($className) {
                return match ($className) {
                    RoleService::class => $this->createMock(RoleService::class),
                    AuthorizationServiceInterface::class => $this->createMock(AuthorizationService::class),
                };
            });

        $container->expects($this->once())
            ->method('has')
            ->with(AuthorizationServiceInterface::class)
            ->willReturn(true);

        $factory              = new AuthorizationServiceFactory();
        $authorizationService = $factory($container, \Lmc\Rbac\Mvc\Service\AuthorizationService::class);
    }

    /**
     * Test the case where Lmc\Rbac\Service\Authorization service is not ser
     */
    public function testMissingBaseAuthorizationService()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())
            ->method('get');

        $container->expects($this->once())
            ->method('has')
            ->with(AuthorizationServiceInterface::class)
            ->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);

        $factory              = new AuthorizationServiceFactory();
        $authorizationService = $factory($container, \Lmc\Rbac\Mvc\Service\AuthorizationService::class);
    }
}
