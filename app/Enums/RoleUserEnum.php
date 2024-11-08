<?php

namespace App\Enums;

use App\Enums\Traits\BaseEnum;

enum RoleUserEnum: string
{
    use BaseEnum;
    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';
}
