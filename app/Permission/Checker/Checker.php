<?php

namespace Fourum\Permission\Checker;

use Fourum\Effect\EffectRepositoryInterface;
use Fourum\Permission\PermissibleInterface;
use Fourum\Permission\Permission;
use Fourum\Permission\PermissionRepositoryInterface;
use Fourum\Permission\PermissiveInterface;

class Checker implements CheckerInterface
{
    /**
     * @var PermissionRepositoryInterface
     */
    protected $permissions;

    /**
     * @var PermissibleInterface
     */
    protected $permissible;

    /**
     * @var EffectRepositoryInterface
     */
    protected $effects;

    /**
     * @param PermissionRepositoryInterface $permissions
     * @param EffectRepositoryInterface $effects
     * @param PermissibleInterface $permissible
     */
    public function __construct(
        PermissionRepositoryInterface $permissions,
        EffectRepositoryInterface $effects,
        PermissibleInterface $permissible = null
    ) {
        $this->permissions = $permissions;
        $this->permissible = $permissible;
        $this->effects = $effects;
    }

    /**
     * @param string $name
     * @param PermissibleInterface $permissible
     * @param PermissiveInterface $permissive
     * @param bool $hard
     * @return bool
     */
    public function check(
        $name,
        PermissibleInterface $permissible,
        PermissiveInterface $permissive = null,
        $hard = false
    ) {
        $permission = $this->permissions->getByNameAndPermissible($name, $permissible, $permissive);

        // effects overrule everything else
        $effects = $this->effects->getEffectsForPermissible($permissible, $name);
        if (! $effects->isEmpty()) {
            $effect = $effects->first();
            return (bool) $effect->getPermissionValue();
        }

        if ($permission && ! (bool) $permission->getValue()) {
            return false;
        }

        if ($hard && ! $permission instanceof Permission) {
            return false;
        }

        return true;
    }

    /**
     * @param $name
     * @param PermissibleInterface $permissible
     * @param PermissiveInterface $permissive
     * @return bool
     */
    public function checkHard($name, PermissibleInterface $permissible, PermissiveInterface $permissive = null)
    {
        return $this->check($name, $permissible, $permissive, true);
    }

    /**
     * @param PermissibleInterface $permissible
     * @param PermissiveInterface $permissive
     * @return bool
     */
    public function supports(PermissibleInterface $permissible, PermissiveInterface $permissive = null)
    {
        return true;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return bool
     */
    public function __call($name, $arguments)
    {
        if ($this->permissible) {
            $name = str_replace('_', '-', $name);
            return $this->check($name, $this->permissible);
        }
    }
}