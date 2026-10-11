<?php

namespace AungKhant\HttpSecurityHeaders\Enums;

enum SecurityPreset: string
{
    case Basic = 'basic';
    case Strict = 'strict';
    case Api = 'api';
}
