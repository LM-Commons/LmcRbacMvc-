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

use Exception;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Lmc\Rbac\Exception\RuntimeException;
use Lmc\Rbac\Mvc\Service\AuthorizationService;
use Lmc\Rbac\Mvc\Service\AuthorizationServiceDelegatorFactory;
use LmcTest\Rbac\Mvc\Asset\AuthorizationAwareFake;
use LmcTest\Rbac\Mvc\Util\ServiceManagerFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use stdClass;

use function method_exists;

/**
 * @covers  \Lmc\Rbac\Mvc\Service\AuthorizationServiceDelegatorFactory
 */
class AuthorizationServiceDelegatorTest extends TestCase
{
    use ProphecyTrait;

    public function testDelegatorFactory()
    {
        $authServiceClassName = AuthorizationService::class;
        $delegator            = new AuthorizationServiceDelegatorFactory();
        $serviceLocator       = $this->prophesize(ServiceLocatorInterface::class);
        $serviceLocator->willImplement(ContainerInterface::class);

        $authorizationService = $this->getMockBuilder(AuthorizationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $callback = function () {
            return new AuthorizationAwareFake();
        };

        $serviceLocator->get($authServiceClassName)->willReturn($authorizationService)->shouldBeCalled();

        /** TODO replace this test */
        $decoratedInstance = $delegator->createDelegatorWithName(
            $serviceLocator->reveal(),
            'name',
            'requestedName',
            $callback
        );

        $this->assertEquals($authorizationService, $decoratedInstance->getAuthorizationService());
    }

    public function testAuthorizationServiceIsNotInjectedWithoutDelegator()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();

        $serviceManager->setAllowOverride(true);
        $authorizationService = $this->getMockBuilder(AuthorizationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService(
            AuthorizationService::class,
            $authorizationService
        );
        $serviceManager->setAllowOverride(false);

        $serviceManager->setInvokableClass(
            'LmcTest\Rbac\Mvc\AuthorizationAware',
            AuthorizationAwareFake::class
        );
        $decoratedInstance = $serviceManager->get('LmcTest\Rbac\Mvc\AuthorizationAware');
        $this->assertNull($decoratedInstance->getAuthorizationService());
    }

    public function testAuthorizationServiceIsInjectedWithDelegatorV3()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();

        if (! method_exists($serviceManager, 'build')) {
            $this->markTestSkipped('this test is only for zend-servicemanager v3');
        }

        $serviceManager->setAllowOverride(true);
//        $authorizationService = $this->getMock('Lmc\Rbac\Mvc\Service\AuthorizationService', [], [], '', false);
        $authorizationService = $this->getMockBuilder(AuthorizationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService(
            AuthorizationService::class,
            $authorizationService
        );
        $serviceManager->setAllowOverride(false);

        $serviceManager->setInvokableClass(
            'LmcTest\Rbac\Mvc\AuthorizationAware',
            AuthorizationAwareFake::class
        );

        $serviceManager->addDelegator(
            AuthorizationAwareFake::class,
            AuthorizationServiceDelegatorFactory::class
        );

        $decoratedInstance = $serviceManager->get('LmcTest\Rbac\Mvc\AuthorizationAware');
        $this->assertEquals($authorizationService, $decoratedInstance->getAuthorizationService());
    }

    public function testDelegatorThrowExceptionWhenBadInterface()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();

        $serviceManager->setAllowOverride(true);
        $authorizationService = $this->getMockBuilder(AuthorizationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService(
            AuthorizationService::class,
            $authorizationService
        );
        $serviceManager->setAllowOverride(false);

        $serviceManager->setFactory(
            'LmcRbacTest\AuthorizationAware',
            function () {
                return new stdClass();
            }
        );

        $serviceManager->addDelegator(
            'LmcRbacTest\AuthorizationAware',
            AuthorizationServiceDelegatorFactory::class
        );

        $thrown = false;
        try {
            $serviceManager->get('LmcRbacTest\AuthorizationAware');
        } catch (Exception $e) {
            $thrown = true;
            $this->assertStringEndsWith(
                'The service LmcRbacTest\AuthorizationAware must implement AuthorizationServiceAwareInterface.',
                $e->getMessage()
            );
            if ($e->getPrevious()) {
                $this->assertInstanceOf(RuntimeException::class, $e->getPrevious());
            }
        }

        $this->assertTrue($thrown);
    }
}
