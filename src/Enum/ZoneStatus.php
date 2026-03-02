<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Enum;

enum ZoneStatus: string
{
    case Initializing = 'initializing';
    case Pending      = 'pending';
    case Active       = 'active';
    case Moved        = 'moved';
}
