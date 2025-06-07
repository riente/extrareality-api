<?php

namespace Extrareality\Enums;

enum RegistrationStatus: string
{
    case NEW = 'new';
    case CONFIRMED = 'confirmed';
    case RESERVE = 'reserve';
    case CANCELLED = 'cancelled';
}
