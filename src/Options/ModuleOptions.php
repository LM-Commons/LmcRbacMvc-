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

namespace Lmc\Rbac\Mvc\Options;

use Lmc\Rbac\Exception;
use Lmc\Rbac\Mvc\Guard\GuardInterface;
use Lmc\Rbac\Mvc\Identity\AuthenticationIdentityProvider;
use Lmc\Rbac\Options\ModuleOptions as BaseModuleOptions;

use function sprintf;

/**
 * Options for LmcRbacMvc module
 */
class ModuleOptions extends BaseModuleOptions
{
    /**
     * Key of the identity provider used to retrieve the identity
     */
    protected string $identityProvider = AuthenticationIdentityProvider::class;

    /**
     * Guards
     */
    protected array $guards = [];

    /**
     * Protection policy for guards (can be "deny" or "allow")
     */
    protected string $protectionPolicy = GuardInterface::POLICY_ALLOW;

    /**
     * Options for the unauthorized strategy
     */
    protected ?UnauthorizedStrategyOptions $unauthorizedStrategyOptions = null;

    /**
     * Options for the redirect strategy
     */
    protected ?RedirectStrategyOptions $redirectStrategyOptions = null;

    /**
     * Guard Plugin Manager configuration
     */
    protected array $guardManager = [];

    /**
     * Constructor
     *
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->__strictMode__ = false;
        parent::__construct($options);
    }

    /**
     * Set the guards options
     */
    public function setGuards(array $guards): void
    {
        $this->guards = $guards;
    }

    /**
     * Get the guards options
     */
    public function getGuards(): array
    {
        return $this->guards;
    }

    /**
     * Set the protection policy for guards
     *
     * @throws Exception\RuntimeException
     */
    public function setProtectionPolicy(string $protectionPolicy): void
    {
        if ($protectionPolicy !== GuardInterface::POLICY_ALLOW && $protectionPolicy !== GuardInterface::POLICY_DENY) {
            throw new Exception\RuntimeException(sprintf(
                'An invalid protection policy was set. Can only be "deny" or "allow", "%s" given',
                $protectionPolicy
            ));
        }

        $this->protectionPolicy = $protectionPolicy;
    }

    /**
     * Get the protection policy for guards
     */
    public function getProtectionPolicy(): string
    {
        return $this->protectionPolicy;
    }

    /**
     * Set the unauthorized strategy options
     */
    public function setUnauthorizedStrategy(array $unauthorizedStrategyOptions): void
    {
        $this->unauthorizedStrategyOptions = new UnauthorizedStrategyOptions($unauthorizedStrategyOptions);
    }

    /**
     * Get the unauthorized strategy options
     */
    public function getUnauthorizedStrategy(): ?UnauthorizedStrategyOptions
    {
        if (null === $this->unauthorizedStrategyOptions) {
            $this->unauthorizedStrategyOptions = new UnauthorizedStrategyOptions();
        }

        return $this->unauthorizedStrategyOptions;
    }

    /**
     * Set the redirect strategy options
     */
    public function setRedirectStrategy(array $redirectStrategyOptions): void
    {
        $this->redirectStrategyOptions = new RedirectStrategyOptions($redirectStrategyOptions);
    }

    /**
     * Get the redirect strategy options
     */
    public function getRedirectStrategy(): ?RedirectStrategyOptions
    {
        if (null === $this->redirectStrategyOptions) {
            $this->redirectStrategyOptions = new RedirectStrategyOptions();
        }

        return $this->redirectStrategyOptions;
    }

    /**
     * Set the key of the identity provider used to retrieve the identity
     */
    public function setIdentityProvider(string $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }

    /**
     * Get the key of the identity provider used to retrieve the identity
     */
    public function getIdentityProvider(): string
    {
        return $this->identityProvider;
    }

    public function setGuardManager(array $config = []): void
    {
        $this->guardManager = $config;
    }

    public function getGuardManager(): array
    {
        return $this->guardManager;
    }
}
