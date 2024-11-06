<?php

namespace App\Enums;

use App\Enums\Traits\BaseEnum;

enum RoleUserEnum
{
    use BaseEnum;
    case ROLE_USER ;
    case ROLE_ADMIN;
}
