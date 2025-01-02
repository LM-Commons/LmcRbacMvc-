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

use Lmc\Rbac\Assertion\AssertionInterface;
use Lmc\Rbac\Identity\IdentityInterface;

/**
 * Minimal interface for an authorization service
 */
interface AuthorizationServiceInterface
{
    /**
     * Check if the permission is granted to the current identity
     */
    public function isGranted(string $permission, mixed $context = null): bool;

    /**
     * Get the current identity from the role service
     */
    public function getIdentity(): ?IdentityInterface;

    /**
     * Set assertions, either merging or replacing (default)
     *
     * @param array<string|callable|AssertionInterface> $assertions
     */
    public function setAssertions(array $assertions, bool $merge = false): void;

    /**
     * Set assertion for a given permission
     */
    public function setAssertion(string $permission, AssertionInterface|callable|string $assertion): void;

    /**
     * Check if there are assertions for the permission
     */
    public function hasAssertion(string $permission): bool;

    /**
     * Get the assertions
     */
    public function getAssertions(): array;

    /**
     * Get the assertions for the given permission
     */
    public function getAssertion(string $permission): AssertionInterface|callable|string|null;
}
