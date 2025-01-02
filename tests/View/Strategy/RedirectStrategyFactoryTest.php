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

namespace LmcTest\Rbac\Mvc\View\Strategy;

use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Lmc\Rbac\Mvc\Options\ModuleOptions;
use Lmc\Rbac\Mvc\Options\RedirectStrategyOptions;
use Lmc\Rbac\Mvc\View\Strategy\RedirectStrategy;
use Lmc\Rbac\Mvc\View\Strategy\RedirectStrategyFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

/**
 * @covers \Lmc\Rbac\Mvc\View\Strategy\RedirectStrategyFactory
 */
class RedirectStrategyFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactory()
    {
        $redirectStrategyOptions = $this->createMock(RedirectStrategyOptions::class);

        $moduleOptionsMock = $this->createMock(ModuleOptions::class);
        $moduleOptionsMock->expects($this->once())
                          ->method('getRedirectStrategy')
                          ->will($this->returnValue($redirectStrategyOptions));

        $authenticationServiceMock = $this->createMock(AuthenticationService::class);

        $serviceLocatorMock = $this->prophesize(ServiceLocatorInterface::class);
        $serviceLocatorMock->willImplement(ContainerInterface::class);
        $serviceLocatorMock->get(ModuleOptions::class)
                           ->willReturn($moduleOptionsMock)
                           ->shouldBeCalled();
        $serviceLocatorMock->get(AuthenticationService::class)
                           ->willReturn($authenticationServiceMock)
                           ->shouldBeCalled();

        $factory          = new RedirectStrategyFactory();
        $redirectStrategy = $factory($serviceLocatorMock->reveal(), RedirectStrategy::class);

        $this->assertInstanceOf(RedirectStrategy::class, $redirectStrategy);
    }
}
