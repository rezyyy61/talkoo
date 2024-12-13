<?php

namespace App\Enums;

enum MessageType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case FILE = 'file';
    case PDF = 'pdf';
    case EMOJI = 'emoji';
    case MIXED = 'mixed';

}
