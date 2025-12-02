<?php

namespace App\Support\Enums;

enum ParticipantGroupStatusEnum: string
{
  case UNPAID = 'unpaid';
  case DP = 'dp';
  case PAID = 'paid';
  case COMPLETED = 'completed';
}
