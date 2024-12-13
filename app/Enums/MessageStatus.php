<?php

namespace App\Enums;

enum MessageStatus: string
{
    case SENT = 'sent';
    case RECEIVED = 'received';
    case READ = 'read';
}
