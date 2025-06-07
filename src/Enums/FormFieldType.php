<?php

namespace Extrareality\Enums;

enum FormFieldType: string
{
    case CHECKBOX = 'checkbox';
    case CHECKBOXES = 'checkboxes';
    case EMAIL = 'email';
    case HIDDEN = 'hidden';
    case NUMBER = 'number';
    case PHONE = 'phone';
    case RADIO = 'radio';
    case SELECT = 'select';
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
}
